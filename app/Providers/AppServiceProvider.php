<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Register console commands
        $this->commands([
            \App\Console\Commands\SystemIntegrityCheck::class,
            \App\Console\Commands\SystemRepairPreview::class,
            \App\Console\Commands\ImportKurriData::class,
        ]);

        // Force HTTPS in production to prevent mixed content issues (like broken images)
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
