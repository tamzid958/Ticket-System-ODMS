<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use PDF;

class TicketPdfController extends Controller
{
    public function download(Request $request)
    {
        $month = $request->query('month', now()->month);
        $year  = $request->query('year', now()->year);

        $tickets = Ticket::query()
            ->when(auth()->user()->hasRole('client'), function ($query) {
                $query->where('client_id', auth()->user()->client_id);
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();

        // Split tickets into regular and excess
        $regularTickets = $tickets->where('is_excess_unit', false);
        $excessTickets  = $tickets->where('is_excess_unit', true);

        $pdf = PDF::loadView('pdf', compact('regularTickets', 'excessTickets', 'month', 'year'));
        return $pdf->stream("tickets_{$month}_{$year}.pdf"); // use stream to open in new tab
    }
}
