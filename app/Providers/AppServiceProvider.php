<?php

namespace App\Providers;

use App\Enums\AppEnvironment;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * Load IDE helper for non-production environment
         *
         * @see https://github.com/barryvdh/laravel-ide-helper
         */
        if ($this->app->isLocal()) {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (in_array(app()->environment(), [
            AppEnvironment::PRODUCTION->value,
            AppEnvironment::UAT->value,
            AppEnvironment::DEVELOPMENT->value,
        ])) {
            $this->app['request']->server->set('HTTPS', 'on');
            URL::forceScheme('https');
        }
    }
}
