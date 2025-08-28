<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketReportController;

Route::get('/', function () {
    return redirect('/panel');
});




Route::middleware('auth')->group(function () {
    // Other authenticated routes...

    // Add this new route for the ticket report
    Route::get('/reports/tickets/{client}/{from_date}/{to_date}', [TicketReportController::class, 'download'])
        ->name('reports.tickets.download');
});