<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\NewCommentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
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
        $recipient = $comment->commentable->user;

        $clientUsers = $client->users;
        $supporters = User::role('support')->get();

        $bcc = $supporters->merge($clientUsers)
            ->reject(fn($user) => $user->id === $recipient->id)
            ->pluck('email')
            ->toArray();

        $recipient->notify(new NewCommentNotification($comment, $bcc));

        Log::info('New Comment Created.', [
            'comment_id' => $comment->id,
            'ticket_id' => $comment->commentable->id,
            'to'        => $recipient->email,
            'bcc'       => $bcc,
        ]);
    }
}
