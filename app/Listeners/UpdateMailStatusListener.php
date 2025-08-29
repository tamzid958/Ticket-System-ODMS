<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Models\Mail;
use App\Enums\MailStatus;

class UpdateMailStatusListener
{
    public function handle(MessageSent $event)
    {
        $ticketId = $this->extractHeader($event->message, 'X-Ticket-ID');
        $userId = $this->extractHeader($event->message, 'X-User-ID');

        if ($ticketId && $userId) {
            Mail::where('ticket_id', $ticketId)
                ->where('user_id', $userId)
                ->where('status', MailStatus::PENDING)
                ->update(['status' => MailStatus::SENT]);
        } else {
            $headers = [];
            foreach ($event->message->getHeaders()->all() as $header) {
                $headers[$header->getName()] = $header->getBodyAsString();
            }
        }
    }

    private function extractHeader($message, string $headerName): ?string
    {
        $header = $message->getHeaders()->get($headerName);
        return $header ? $header->getBodyAsString() : null;
    }
}