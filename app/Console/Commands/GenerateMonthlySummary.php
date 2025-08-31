<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientMonthlySummary;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:client-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly summaries per client';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lastMonth = Carbon::now()->subMonth();
        $start = $lastMonth->copy()->startOfMonth();

        foreach (Client::all() as $client) {

            ClientMonthlySummary::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'year' => $lastMonth->year,
                    'month' => $lastMonth->month,
                ],
                [
                    'month' => $lastMonth->month,
                    // todo:: Add data for summary
                ]
            );

            $this->info("Summary for client [{$client->name}] for {$start->format('F Y')} generated.");
        }

        $this->info("Client monthly summary generation for {$start->format('F Y')} completed.");
    }
}
