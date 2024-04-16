<?php

namespace App\Notifications;

use App\Enums\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SlackSystemAlertNotification extends Notification implements ShouldQueue
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
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(mixed $notifiable): SlackMessage
    {
        $title = config('app.name')." System Notification: $this->level";
        $message = $this->message;

        return (new SlackMessage())
            ->error()
            ->attachment(function (SlackAttachment $attachment) use ($title, $message) {
                $attachment->title($title)->content($message);
            });
    }
}
