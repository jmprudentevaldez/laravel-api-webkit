<?php

namespace App\Providers;

use App\Interfaces\Authentication\TokenAuthServiceInterface;
use App\Interfaces\HttpResources\UserServiceInterface;
use App\Models\User;
use App\Services\Authentication\TokenAuthService;
use App\Services\HttpResources\UserService;
use Illuminate\Support\ServiceProvider;

class HttpResourceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, function () {
            return new UserService(new User());
        });
        $this->app->bind(TokenAuthServiceInterface::class, function () {
            return new TokenAuthService(new User());
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
