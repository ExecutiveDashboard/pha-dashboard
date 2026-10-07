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
        // Dynamically resolve SQLite database path to ensure it always points to the active project folder.
        // This prevents exceptions caused by configuration caching or moving/renaming the project directory.
        if (config('database.default') === 'sqlite') {
            $db = config('database.connections.sqlite.database');
            if (empty($db) || !file_exists($db) || str_ends_with(str_replace('\\', '/', $db), '/database/database.sqlite')) {
                config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
            }
        }
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
