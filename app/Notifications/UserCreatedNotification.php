<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $tempPassword;

    /**
     * Create a new notification instance.
     * @param string $tempPassword
     */
    public function __construct(string $tempPassword)
    {
        $this->tempPassword = $tempPassword;
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
            ->subject(config('app.name') . " :: New Account for $notifiable->name")
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your account has been created.')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Temporary Password:** ' . $this->tempPassword)
            ->line('Please change your password immediately after login.')
            ->action('Change Password', url('https://support-odms.innovatorslab.net/panel/profile'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
