<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Ticket;

class SupportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public Ticket $ticket;

    public function __construct(string $subjectLine, Ticket $ticket)
    {
        $this->subjectLine = $subjectLine;
        $this->ticket = $ticket;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
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
                'ticketDescription'=> $this->ticket->description,
                'createdAt'       => $this->ticket->created_at->format('d M Y, H:i'),
                'ticketUrl' => config('app.url') . '/panel/tickets/' . $this->ticket->id
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}