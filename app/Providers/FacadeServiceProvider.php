<?php

namespace App\Providers;

use App\Helpers\ConversionHelper;
use App\Helpers\DateTimeHelper;
use App\Helpers\PaginationHelper;
use Illuminate\Support\ServiceProvider;

/**
 * Bind all custom-made Facades here
 */
class FacadeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('PaginationHelper', function ($app) {
            return new PaginationHelper();
        });
        $this->app->bind('DateTimeHelper', function ($app) {
            return new DateTimeHelper();
        });
        $this->app->bind('ConversionHelper', function ($app) {
            return new ConversionHelper();
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
