<?php

use Illuminate\Support\Facades\Route;
use App\Services\IcsHolidayService;
use Carbon\Carbon;
use App\Http\Controllers\TicketPdfController;

Route::get('/', function () {
    return redirect('/panel');
});

// for PDF download
Route::get('/tickets/pdf', [TicketPdfController::class, 'download'])
    ->name('tickets.pdf');

// TODO: temporary testing routes, remove later
Route::get('/api/today-holiday', function () {
    $service = new IcsHolidayService();
    $today = Carbon::today();

    return [
        'date' => $today->toDateString(),
        'is_holiday' => $service->isHoliday($today),
    ];
});

Route::get('/api/holidays/{year}', function ($year) {
    $service = new IcsHolidayService();

    return [
        'year' => $year,
        'holidays' => $service->getHolidays((int) $year),
    ];
});

Route::get('/api/is-holiday/{date}', function ($date) {
    $service = new IcsHolidayService();
    $carbonDate = Carbon::parse($date);

    return [
        'date' => $carbonDate->toDateString(),
        'is_holiday' => $service->isHoliday($carbonDate),
    ];
});