<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('identity:purge-expired')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('exports:purge-expired')->hourly()->withoutOverlapping();

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
