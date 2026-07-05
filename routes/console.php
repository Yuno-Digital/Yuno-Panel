<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatically poll every node's daemon so the panel keeps each node's online
// status, memory and disk up to date without manual intervention.
Schedule::command('nodes:refresh')
    ->everyMinute()
    ->withoutOverlapping();

// Run any server scheduled tasks (timed power actions / console commands) whose
// cron time has come due.
Schedule::command('schedules:run')
    ->everyMinute()
    ->withoutOverlapping();
