<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Note: Order reminders are triggered via JS timer in admin.blade.php
// The artisan command is still available for manual testing: php artisan reminders:send-orders

use Schedule;

Schedule::command('orders:sync-tracking')->hourly();
Schedule::command(sprintf('email:sync --limit=%d', (int) config('gmail.sync.per_page', 50)))
    ->everyFiveMinutes()
    ->withoutOverlapping();
