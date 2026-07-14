<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('system:integrity-check')
    ->dailyAt('02:00')
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Critical system integrity check failed during scheduled run.');
    });
