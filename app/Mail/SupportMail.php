<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use App\Models\Ticket;
use App\Models\Mail;
use App\Enums\MailStatus;
use Throwable;
use Illuminate\Support\Facades\Log;

class SupportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public Ticket $ticket;
    public string $userId;

    public function __construct(string $subjectLine, Ticket $ticket, string $userId)
    {
        $this->subjectLine = $subjectLine;
        $this->ticket = $ticket;
        $this->userId = $userId;
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: $this->subjectLine
        );

        return $envelope;
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Ticket-ID' => (string) $this->ticket->id,
                'X-User-ID' => (string) $this->userId,
            ]
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.support',
            with: [
                'clientName'      => $this->ticket->client->name ?? 'Unknown',
                'clientId'        => $this->ticket->client_id,
                'creatorName'     => $this->ticket->user->name ?? 'Unknown',
                'ticketTitle'     => $this->ticket->subject,
                'ticketDescription' => $this->ticket->description,
                'createdAt'       => $this->ticket->created_at->format('d M Y, H:i'),
                'ticketUrl'       => config('app.url') . '/panel/tickets/' . $this->ticket->id
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function failed(Throwable $exception): void
    {
        Mail::where('ticket_id', $this->ticket->id)
            ->where('user_id', $this->userId)
            ->where('status', MailStatus::PENDING)
            ->update(['status' => MailStatus::FAILED]);

        Log::error("Failed to send email for ticket ID {$this->ticket->id}, user ID {$this->userId}: {$exception->getMessage()}");
    }
}