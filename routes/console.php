<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule email checking every 5 minutes
Schedule::command('emails:check')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule session cleanup daily at 2 AM
Schedule::command('sessions:cleanup')
    ->dailyAt('02:00')
    ->runInBackground();
