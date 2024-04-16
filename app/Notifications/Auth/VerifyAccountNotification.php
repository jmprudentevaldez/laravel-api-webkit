<?php

namespace App\Notifications\Auth;

use App\Enums\Queue;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class VerifyAccountNotification extends QueuedVerifyEmailNotification implements ShouldQueue
{
    use Queueable;

    private string $temporaryPassword;

    private int $expirationTimeMinutes;

    private User $user;

    public function __construct(mixed $notifiable, string $temporaryPassword)
    {
        parent::__construct($notifiable);
        $this->onQueue(Queue::EMAILS->value);
        $this->temporaryPassword = $temporaryPassword;
        $this->user = $notifiable;
        $this->expirationTimeMinutes = Config::get('auth.verification.expiration.account', 10080);
    }

    /** {@inheritDoc} */
    protected function buildMailMessage($url): MailMessage
    {
        $appName = config('app.name');
        $safeEmail = urlencode($this->user->email);

        $url .= "&email=$safeEmail";
        $expirationTimeDays = floor($this->expirationTimeMinutes / 1440);

        return (new MailMessage())
            ->subject('Verify Your Account')
            ->greeting('Hey, '.$this->notifiableName.'!')
            ->line("A $appName account has been created for you.")
            ->line('Your temporary password: '.$this->temporaryPassword)
            ->line('Please change this immediately after logging in')
            ->line(
                "Please click the button below to complete the verification process of your account. 
                    Please note that this link will expire in $expirationTimeDays days."
            )
            ->action('Verify Account', $url);
    }

    /** {@inheritDoc} */
    protected function getFrontEndUrl(): string
    {
        return config('clients.web.url.verify-account');
    }

    /** {@inheritDoc} */
    protected function getExpirationTime(): Carbon
    {
        return Carbon::now()->addMinutes($this->expirationTimeMinutes);
    }
}
