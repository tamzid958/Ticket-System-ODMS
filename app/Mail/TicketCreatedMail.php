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
use Throwable;
use Illuminate\Support\Facades\Log;

class TicketCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "ODMS Support:: New Ticket Created #{$this->ticket->id}"
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Ticket-ID' => (string) $this->ticket->id
            ]
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.ticket-created',
            with: [
                'clientName'      => $this->ticket->client->name ?? 'Unknown',
                'clientId'        => $this->ticket->client->id,
                'creatorName'     => $this->ticket->user->name ?? 'Unknown',
                'ticketTitle'     => $this->ticket->subject,
                'ticketDescription' => $this->ticket->description,
                'createdAt'       => $this->ticket->created_at->format('d M Y, H:i'),
                'ticketUrl'       => url('/panel/tickets/' . $this->ticket->id)
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Failed to send email for ticket ID {$this->ticket->id}", ['exception' => $exception]);
    }
}
