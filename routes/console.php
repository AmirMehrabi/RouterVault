<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:check-access-point-status')->everyMinute()->withoutOverlapping();
Schedule::command('app:refresh-wireless-client-management')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('routers:check-connectivity')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('backups:run-due')->everyMinute()->withoutOverlapping();
