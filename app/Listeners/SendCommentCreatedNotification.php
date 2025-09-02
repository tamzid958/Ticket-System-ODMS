<?php

namespace App\Listeners;

use App\Enums\MailStatus;
use App\Mail\SupportMail;
use App\Models\User;
use App\Notifications\NewCommentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Kirschbaum\Commentions\Events\CommentWasCreatedEvent;

class SendCommentCreatedNotification implements ShouldQueue
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
    public function handle(CommentWasCreatedEvent $event): void
    {
        $comment = $event->comment;
        $client = $comment->commentable->client;

        $clientUsers = $client->users;
        $supporters = User::role('support')->get();

        $recipients = $supporters->merge($clientUsers);

        Notification::send($recipients, new NewCommentNotification($comment));
    }
}
