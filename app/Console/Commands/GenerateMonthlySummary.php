<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Client;
use App\Models\ClientMonthlySummary;
use App\Services\ClientMonthlySummaryService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientMonthlySummaryGeneratedMail;

class GenerateMonthlySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:client-monthly {month?} {year?}';

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
        $summaryMonthInt = $this->argument('month');
        $summaryYearInt = $this->argument('year');

        $summaryMonth = Carbon::now();

        if ($summaryMonthInt) {
            $summaryMonth->month($summaryMonthInt);
        }

        if ($summaryYearInt) {
            $summaryMonth->year($summaryYearInt);
        }

        if (!$summaryMonthInt && !$summaryYearInt) {
            $summaryMonth->subMonth();
        }

        $summaryMonth->startOfMonth();

        foreach (Client::all() as $client) {

            $clientMonthlySummaryRecord = ClientMonthlySummary::where('client_id', $client->id)
                ->where('year', $summaryMonth->year)
                ->where('month', $summaryMonth->month)
                ->first();

            if (!$clientMonthlySummaryRecord && $client->will_generate_report) {

                $clientMonthlySummary = $this->clientMonthlySummaryService->getMonthlyClientSummary(
                    $client->id,
                    $summaryMonth->year,
                    $summaryMonth->month,
                );

                // Create new record
                ClientMonthlySummary::create([
                    'client_id' => $client->id,
                    'year' => $summaryMonth->year,
                    'month' => $summaryMonth->month,
                    'package_id' => $clientMonthlySummary['package_id'],
                    'monthly_quota' => $clientMonthlySummary['monthly_quota'],
                    'excess_per_unit_price' => $clientMonthlySummary['excess_per_unit_price'],
                    'package_price' => $clientMonthlySummary['package_price'],
                    'package_discount' => $clientMonthlySummary['package_discount'],
                    'unit_used' => $clientMonthlySummary['unit_used'],
                    'excess_unit_used' => $clientMonthlySummary['excess_unit_used'],
                    'unit_discount' => $clientMonthlySummary['unit_discount'],
                    'excess_unit_cost' => $clientMonthlySummary['excess_unit_cost'],
                    'total_cost' => $clientMonthlySummary['total_cost'],
                ]);

                $this->info("Summary for client [{$client->name}] for {$summaryMonth->format('F Y')} generated.");
                

         
                $users = User::all();

                foreach ($users as $user) {
                    if ($user->hasRole('super_admin')) { 
                        Mail::to($user->email)
                            ->queue(new ClientMonthlySummaryGeneratedMail($clientMonthlySummaryRecord));
                    }
                }

            }
        }
    }
}
