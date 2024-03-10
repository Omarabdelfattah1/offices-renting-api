<?php

namespace App\Notifications;

use App\Models\Office;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfficePendingNotification extends Notification
{
    use Queueable;

    public $office = null;
    /**
     * Create a new notification instance.
     */
    public function __construct(Office $office)
    {
        $this->office = $office;
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
                    ->subject('Office Updated')
                    ->greeting('Hello!')
                    ->line('You have an office updated!');
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
