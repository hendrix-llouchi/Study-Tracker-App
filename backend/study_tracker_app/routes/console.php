<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule tasks
Schedule::command('app:send-morning-emails')->hourly();
Schedule::command('app:send-reminders')->hourly();
Schedule::command('app:generate-weekly-reports')->hourly();
Schedule::command('app:update-assignment-statuses')->hourly();
Schedule::command('app:cleanup-old-data')->dailyAt('02:00');
