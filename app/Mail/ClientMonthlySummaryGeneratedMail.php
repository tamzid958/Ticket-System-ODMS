<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientMonthlySummaryGeneratedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $zipPath;
    public Carbon $month;

    public function __construct(string $zipPath, Carbon $month)
    {
        $this->zipPath = $zipPath;
        $this->month = $month;
    }

    public function build()
    {
        return $this->subject("Client Monthly Reports – {$this->month->format('F Y')}")
                    ->view('mail.client_monthly_summary_generated')
                    ->with([
                        'month' => $this->month, // ✅ pass $month to the view
                    ])
                    ->attach($this->zipPath, [
                        'as' => "All-Reports-{$this->month->format('F-Y')}.zip",
                        'mime' => 'application/zip',
                    ]);
    }
}
