<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send order reminders every 4 hours during business hours (9 AM - 9 PM)
Schedule::command('reminders:send-orders')
    ->everyFourHours()
    ->between('09:00', '21:00')
    ->withoutOverlapping();
