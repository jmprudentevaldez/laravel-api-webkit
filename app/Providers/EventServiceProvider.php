<?php

namespace App\Providers;

use App\Events\UserCreated;
use App\Events\UserRegistered;
use App\Listeners\LogEventListener;
use App\Listeners\SendVerifyAccountNotification;
use App\Listeners\SendVerifyEmailNotification;
use App\Listeners\SendWelcomeEmailNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Log\Events\MessageLogged;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserCreated::class => [
            SendWelcomeEmailNotification::class,
            SendVerifyAccountNotification::class,
        ],
        UserRegistered::class => [
            SendWelcomeEmailNotification::class,
            SendVerifyEmailNotification::class,
        ],
        MessageLogged::class => [
            LogEventListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
