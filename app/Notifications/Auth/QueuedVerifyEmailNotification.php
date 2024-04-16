<?php

namespace App\Notifications\Auth;

use App\Enums\Queue;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

/**
 * Added a bit of customization to Laravel's default
 * verify email notification: Queueable and mail content edits
 */
class QueuedVerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    protected string $notifiableName;

    private int $expirationTimeMinutes;

    public function __construct(mixed $notifiable)
    {
        /** @var User $notifiable */
        $this->notifiableName = $notifiable->userProfile->first_name;
        $this->onQueue(Queue::EMAILS->value);
        $this->expirationTimeMinutes = Config::get('auth.verification.expiration.email', 60);
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return $this->buildMailMessage($verificationUrl);
    }

    /**
     * Get the verify-email notification mail message for the given URL.
     *
     * @param  string  $url
     */
    protected function buildMailMessage($url): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage())
            ->subject('Verify Your Email Address')
            ->greeting('Hey, '.$this->notifiableName.'!')
            ->line("Thank you for registering to $appName.")
            ->line(
                "Please click the button below to verify your email address. 
               Please note that this link will expire in $this->expirationTimeMinutes minutes."
            )
            ->action('Verify Email Address', $url)
            ->line('If you did not create an account, please ignore this email.');
    }

    /**
     * Overwrite the default verification URL as it points back to the
     * API endpoint and not the SPA
     *
     * @return mixed|string
     */
    protected function verificationUrl($notifiable): mixed
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        // this returns https://<api.domain.com>/api/v1/auth/email/verify/<id>/<hash>?expires=<value>&signature=<value>
        $apiRoute = URL::temporarySignedRoute(
            'verification.verify',
            $this->getExpirationTime(),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            false
        );

        // returns /api/v1/auth/email/verify/1/1
        $apiBase = route('verification.verify', ['id' => 1, 'hash' => 1], false);

        // strip the id and hash value
        $apiBase = explode('1/1', $apiBase)[0];

        // transform to: https://spa.domain.com/auth/verify-email/<id>/<hash>?expires=<value>&signature=<value>
        $frontEndUrl = $this->getFrontEndUrl();

        return $frontEndUrl.'/'.explode($apiBase, $apiRoute)[1];
    }

    /**
     * Create the front-end URL
     */
    protected function getFrontEndUrl(): string
    {
        return config('clients.web.url.verify-email');
    }

    /**
     * Set the expiration time of URL
     */
    protected function getExpirationTime(): Carbon
    {
        return Carbon::now()->addMinutes($this->expirationTimeMinutes);
    }
}
