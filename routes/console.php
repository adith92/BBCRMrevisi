<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule subscription billing daily at 06:00 WIB (23:00 UTC)
Schedule::command('subscriptions:bill')->dailyAt('23:00');
