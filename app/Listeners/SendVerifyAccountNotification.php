<?php

namespace App\Listeners;

use App\Events\UserCreated;

class SendVerifyAccountNotification
{
    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        if (! $event->user->hasVerifiedEmail()) {
            $event->user->sendAccountVerificationNotification($event->temporaryPassword);
        }
    }
}
