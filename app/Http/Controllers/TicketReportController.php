<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketReportController extends Controller
{
    public function download(Client $client, string $year, string $month)
    {
        if (auth()->user()->client_id !== null && auth()->user()->client_id != $client->id) {
            abort(403, 'Unauthorized action.');
        }

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

        $totalPackageAmount = $client->package->base_price;
        $totalExcessAmount = $excessTickets->sum('unit_value');
        $grandTotal = $totalPackageAmount + $totalExcessAmount;

        $data = compact(
            'client',
            'regularTickets',
            'excessTickets',
            'fromDate',
            'toDate',
            'totalPackageAmount',
            'totalExcessAmount',
            'grandTotal'
        );

        $pdf = Pdf::loadView('pdfs.ticket_report', $data);

        return $pdf->download("Report-{$client->name}-{$fromDate->format('F-Y')}.pdf");
    }

}