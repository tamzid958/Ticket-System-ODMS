<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientMonthlySummary;
use App\Services\ClientMonthlySummaryService;
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

    protected ClientMonthlySummaryService $clientMonthlySummaryService;

    public function __construct(ClientMonthlySummaryService $clientMonthlySummaryService)
    {
        parent::__construct();
        $this->clientMonthlySummaryService = $clientMonthlySummaryService;
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lastMonth = Carbon::now()->subMonth();
        $start = $lastMonth->copy()->startOfMonth();

        foreach (Client::all() as $client) {

            $clientMonthlySummary = $this->clientMonthlySummaryService->getMonthlyClientSummary(
                $client->id,
                $lastMonth->year,
                $lastMonth->month,
            );

            ClientMonthlySummary::firstOrCreate(
                [
                    'client_id' => $client->id,
                    'year' => $lastMonth->year,
                    'month' => $lastMonth->month,
                ],
                [
                    'monthly_quota' => $clientMonthlySummary['monthly_quota'],
                    'unit_used' => $clientMonthlySummary['unit_used'],
                    'excess_unit_used' => $clientMonthlySummary['excess_unit_used'],
                    'unit_discount' => $clientMonthlySummary['unit_discount'],
                    'package_price' => $clientMonthlySummary['package_price'],
                    'package_discount' => $clientMonthlySummary['package_discount'],
                    'excess_unit_cost' => $clientMonthlySummary['excess_unit_cost'],
                    'total_cost' => $clientMonthlySummary['total_cost'],
                ]
            );

            $this->info("Summary for client [{$client->name}] for {$start->format('F Y')} generated.");
        }

        $this->info("Client monthly summary generation for {$start->format('F Y')} completed.");
    }
}
