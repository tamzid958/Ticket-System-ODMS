<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ClientMonthlySummary;

class ClientMonthlySummaryGeneratedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ClientMonthlySummary $summary;

    public function __construct(ClientMonthlySummary $summary)
    {
        $this->summary = $summary;
    }

    public function build()
    {
        return $this->subject("Monthly Summary Generated: Client #{$this->summary->client_id}")
                    ->view('mail.client-monthly-summary')
                    ->with([
                        'clientName' => $this->summary->client->name,
                        'month' => $this->summary->month,
                        'year' => $this->summary->year,
                        'totalCost' => $this->summary->total_cost,
                        'summaryUrl' => url("/panel/client-monthly-summaries/{$this->summary->id}"),
                    ]);
    }
}
