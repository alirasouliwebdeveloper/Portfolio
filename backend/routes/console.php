<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('uploads:cleanup')->hourly();
// In the schedule:run process itself: a separate PHP process every minute was being killed by the
// shared host's memory limit (exit 139/255 in the log).
Schedule::call(fn () => Artisan::call('content:publish-scheduled'))
    ->name('content:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping();
