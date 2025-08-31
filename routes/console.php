<?php

use App\Console\Commands\GenerateMonthlySummary;
use \Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(GenerateMonthlySummary::class)
    ->monthlyOn(1, '00:10')
    ->withoutOverlapping();
