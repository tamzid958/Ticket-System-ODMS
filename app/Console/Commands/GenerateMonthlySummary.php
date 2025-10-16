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
use App\Http\Controllers\TicketReportController;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GenerateMonthlySummary extends Command
{
    protected $signature = 'summary:client-monthly {month?} {year?}';
    protected $description = 'Generate monthly summaries per client';

    protected ClientMonthlySummaryService $clientMonthlySummaryService;
    protected TicketReportController $ticketReportController;

    public function __construct(
        ClientMonthlySummaryService $clientMonthlySummaryService,
        TicketReportController $ticketReportController
    ) {
        parent::__construct();
        $this->clientMonthlySummaryService = $clientMonthlySummaryService;
        $this->ticketReportController = $ticketReportController;
    }

    public function handle()
    {
        $summaryMonthInt = $this->argument('month');
        $summaryYearInt = $this->argument('year');
        $summaryMonth = Carbon::now();

        if ($summaryMonthInt) $summaryMonth->month($summaryMonthInt);
        if ($summaryYearInt) $summaryMonth->year($summaryYearInt);
        if (!$summaryMonthInt && !$summaryYearInt) $summaryMonth->subMonth();

        $summaryMonth->startOfMonth();

        $generatedClients = [];

        foreach (Client::all() as $client) {
            $clientMonthlySummaryRecord = ClientMonthlySummary::where('client_id', $client->id)
                ->where('year', $summaryMonth->year)
                ->where('month', $summaryMonth->month)
                ->first();

            if (!$clientMonthlySummaryRecord && $client->is_report_generated_by_default) {
                $clientMonthlySummary = $this->clientMonthlySummaryService->getMonthlyClientSummary(
                    $client->id,
                    $summaryMonth->year,
                    $summaryMonth->month,
                );

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

                $generatedClients[] = $client;
            }
        }

        // Only proceed if there are generated reports
        if (count($generatedClients) > 0) {
            $this->info('Generating report PDFs and sending email...');

            $now = $summaryMonth;
            $zipFileName = "All-Reports-{$now->format('F-Y')}.zip";
            $zipPath = storage_path("app/{$zipFileName}");

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($generatedClients as $client) {
                    $response = $this->ticketReportController->download($client, $now->year, str_pad($now->month, 2, '0', STR_PAD_LEFT));
                    $pdfContent = $response->getOriginalContent();
                    $zip->addFromString("{$client->name}-{$now->format('F-Y')}.pdf", $pdfContent);
                }
                $zip->close();
            }


                  
            $users = User::all();
            $superAdmins = [];

            foreach ($users as $user) {
                if ($user->hasRole('super_admin')) { 
                    $superAdmins[] = $user;
                }
            }
            $superAdmins = collect($superAdmins);
            $to = $superAdmins->first()?->email;
            $cc = $superAdmins->skip(1)->pluck('email')->toArray();

            if ($to) {
                Mail::to($to)
                    ->cc($cc)
                    ->send(new ClientMonthlySummaryGeneratedMail($zipPath, $now));
                $this->info("Email sent to super admins with all reports attached.");
            }

            // delete the zip file after sending
            Storage::delete("app/{$zipFileName}");
        } else {
            $this->info('No new client reports generated. No email sent.');
        }
    }
}
