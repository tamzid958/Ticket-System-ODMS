<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketReportController;

Route::get('/', function () {
    return redirect('/panel');
});




Route::middleware('auth')->group(function () {
Route::get('/reports/tickets/{client}/{year}/{month}', [TicketReportController::class, 'download'])
    ->name('reports.tickets.download');

});