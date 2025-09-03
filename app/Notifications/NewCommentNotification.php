<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;
    protected $bcc;

    /**
     * Create a new notification instance.
     */
    public function __construct($comment, $bcc)
    {
        $this->comment = $comment;
        $this->bcc = $bcc;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name') . " :: New Comment on Ticket #{$this->comment->commentable->id}")
            ->line("A new comment was added for the ticket: {$this->comment->commentable->subject}")
            ->action('View Comment', url("/panel/tickets/{$this->comment->commentable->id}"))
            ->bcc($this->bcc);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'commentable_id' => $this->comment->commentable_id,
            'commentable_type' => $this->comment->commentable_type,
            'message' => $this->comment->message,
        ];
    }
}
