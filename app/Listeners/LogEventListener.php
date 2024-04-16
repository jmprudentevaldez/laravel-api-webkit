<?php

namespace App\Listeners;

use App\Enums\AppEnvironment;
use App\Enums\Permission;
use App\Models\User;
use App\Notifications\EmailSystemAlertNotification;
use App\Notifications\SlackSystemAlertNotification;
use Illuminate\Log\Events\MessageLogged;

class LogEventListener
{
    public const LOG_LEVELS = [
        'debug' => 1,
        'info' => 2,
        'notice' => 3,
        'warning' => 4,
        'error' => 5,
        'critical' => 6,
        'alert' => 7,
        'emergency' => 8,
    ];

    /**
     * Handle the event.
     */
    public function handle(MessageLogged $event): void
    {
        // Only send email notifications when in prod, uat, or development
        if (! in_array(app()->environment(),
            [
                AppEnvironment::PRODUCTION->value,
                AppEnvironment::UAT->value,
                AppEnvironment::DEVELOPMENT->value,
            ]
        )) {
            return;
        }

        // check the logging level set in config
        if (self::LOG_LEVELS[$event->level] < self::LOG_LEVELS[config('logging.event_listener_level')]) {
            return;
        }

        // send a notification to all users with the system alert permission
        $users = User::permission([Permission::RECEIVE_SYSTEM_ALERTS->value])->cursor();
        $slackAlertSent = false;
        /** @var User $user */
        foreach ($users as $user) {
            // We only send the slack alert once
            if (! $slackAlertSent) {
                $user->notify(new SlackSystemAlertNotification($event->level, $event->message));
                $slackAlertSent = true;
            }

            // We send email alerts to every System Support Role
            $user->notify(new EmailSystemAlertNotification($event->level, $event->message));
        }
    }
}
