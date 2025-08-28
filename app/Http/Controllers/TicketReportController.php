<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketReportController extends Controller
{
    public function download(Client $client, string $from_date, string $to_date)
    {
        // 1. Authorize the request (optional but recommended)
        // Ensure the logged-in user can view this client's report
        if (auth()->user()->client_id !== null && auth()->user()->client_id != $client->id) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Eager load the package relationship
        $client->load('package');
        if (!$client->package) {
            // Handle case where client might not have a package assigned
            return response('Client package not found.', 404);
        }

        // 3. Parse dates from the route
        $fromDate = Carbon::parse($from_date)->startOfDay();
        $toDate = Carbon::parse($to_date)->endOfDay();

        // 4. Query the tickets based on client and date range
        $tickets = Ticket::where('client_id', $client->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('created_at', 'asc')
            ->get();

        $regularTickets = $tickets->where('is_excess_unit', false);
        $excessTickets = $tickets->where('is_excess_unit', true);

        // 5. Perform calculations for the summary
        $numberOfMonths = $fromDate->diffInMonths($toDate->copy()->addDay()) ?: 1; // Calculate months inclusively
        $totalPackageAmount = $client->package->base_price * $numberOfMonths;
        $totalExcessAmount = $excessTickets->sum('unit_value');
        $grandTotal = $totalPackageAmount + $totalExcessAmount;

        // 6. Prepare data for the view
        $data = [
            'client' => $client,
            'regularTickets' => $regularTickets,
            'excessTickets' => $excessTickets,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'numberOfMonths' => $numberOfMonths,
            'totalPackageAmount' => $totalPackageAmount,
            'totalExcessAmount' => $totalExcessAmount,
            'grandTotal' => $grandTotal,
        ];

        // 7. Load the view, generate the PDF, and stream it to the browser
        $pdf = Pdf::loadView('pdfs.ticket_report', $data);

        // Use stream() to open in a new tab instead of forcing a download
        return $pdf->stream("Report-{$client->name}-{$fromDate->format('Y-m-d')}.pdf");
    }
}