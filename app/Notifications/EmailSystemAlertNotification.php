<?php

namespace App\Notifications;

use App\Enums\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailSystemAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $level;

    private string $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $level, string $message)
    {
        $this->onQueue(Queue::NOTIFICATIONS->value);
        $this->level = strtoupper($level);
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * @Channel
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->greeting("System Notification: $this->level")
            ->level($this->level)
            ->line('Issue: ')
            ->line($this->message)
            ->line(
                "You received this notification because you've been registered 
                        with a System Support role"
            );
    }
}
