<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogSentMessage implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $message = $event->message;


        Log::info('Mail sent successfully.', [
            'subject' => $message->getSubject(),
            'to' => $message->getTo() ?? [],
            'cc' => $message->getCc() ?? [],
            'bcc' => $message->getBcc() ?? [],
        ]);
    }
}
