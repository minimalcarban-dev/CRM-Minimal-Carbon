<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Note: Order reminders are triggered via JS timer in admin.blade.php
// The artisan command is still available for manual testing: php artisan reminders:send-orders

Schedule::command('orders:sync-tracking')->hourly();
Schedule::command(sprintf('email:sync --limit=%d', (int) config('gmail.sync.per_page', 50)))
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Keep duration snapshots aligned for reports while dynamic accessors stay real-time accurate.
Schedule::command('diamonds:sync-duration --chunk=500')
    ->dailyAt('02:15')
    ->withoutOverlapping();

// Safe background queue processing for shared cPanel hosting
// It processes any pending jobs and stops. Max time ensures it doesn't run endlessly.
Schedule::command('queue:work --stop-when-empty --max-time=240 --memory=128 --tries=3')
    ->everyFiveMinutes()
    ->withoutOverlapping();
