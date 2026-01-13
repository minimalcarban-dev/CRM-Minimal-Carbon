<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Note: Order reminders are triggered via JS timer in admin.blade.php
// The artisan command is still available for manual testing: php artisan reminders:send-orders
