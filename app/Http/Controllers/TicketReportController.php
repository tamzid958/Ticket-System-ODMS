<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use ZipArchive;

class TicketReportController extends Controller
{
    public function download(Client $client, string $year, string $month)
    {
        if (auth()->user()->hasAnyRole(['client',]) && auth()->user()->client_id != $client->id) {
            abort(403, 'Unauthorized action.');
        }

        $costingDate = Carbon::createFromDate((int)$year, (int)$month, 1);
        $client = $client->versionedInstance($costingDate);
        $client->load('package');

        if (!$client->package) {
            return response('Client package not found.', 404);
        }

        $fromDate = Carbon::create($year, $month, 1)->startOfMonth();
        $toDate = $fromDate->copy()->endOfMonth();

        $tickets = Ticket::where('client_id', $client->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('created_at', 'asc')
            ->get();

        $regularTickets = $tickets->where('is_excess_unit', false);
        $excessTickets = $tickets->where('is_excess_unit', true);

        // ===== Calculate count-multiplier triplets =====
        $countMultipliers = [];

        foreach ($regularTickets as $ticket) {
            $multiplier = ($ticket->timeslot_multiplier ?? 1) * ($ticket->holiday_multiplier ?? 1) * ($ticket->ticket_weight ?? 1);
            $unitDiscount = $ticket->unit_discount ?? 0;

            $found = false;
            foreach ($countMultipliers as &$pair) {
                if ($pair[1] === $multiplier) {
                    $pair[0] += 1; // increment ticket count
                    $pair[2] += $unitDiscount; // accumulate discount
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $countMultipliers[] = [1, $multiplier, $unitDiscount];
            }
        }

        $package = $client->package->versionedInstance($costingDate);
        $totalPackageAmount = $package->base_price;
        $packageDiscount = $client->discount;
        $totalExcessAmount = $excessTickets->sum('unit_value');
        $excessUnitPrice = $client->package->excess_unit_price;
        $grandTotal = $totalPackageAmount + $totalExcessAmount;

        $data = compact(
            'package',
            'client',
            'regularTickets',
            'excessTickets',
            'fromDate',
            'toDate',
            'totalPackageAmount',
            'packageDiscount',
            'totalExcessAmount',
            'grandTotal',
            'countMultipliers',
            'excessUnitPrice'
        );

        $pdf = Pdf::loadView('pdfs.ticket_report', $data);

        return $pdf->download("Report-{$client->name}-{$fromDate->format('F-Y')}.pdf");
    }

    public function downloadAllPreviousMonth()
{
    $now = Carbon::now('Asia/Dhaka')->subMonth();
    $year = $now->year;
    $month = $now->month;

    $clients = Client::all();

    $zipFileName = "All-Reports-{$now->format('F-Y')}.zip";
    $zipPath = storage_path("app/{$zipFileName}");

    $zip = new ZipArchive;
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        foreach ($clients as $client) {
            // reuse existing download() logic, but capture the PDF instead of streaming
            $response = $this->download($client, $year, str_pad($month, 2, '0', STR_PAD_LEFT));
            $pdfContent = $response->getOriginalContent();

            $zip->addFromString("{$client->name}-{$now->format('F-Y')}.pdf", $pdfContent);
        }
        $zip->close();
    }

    return response()->download($zipPath)->deleteFileAfterSend(true);
}

}
