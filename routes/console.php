<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('mail:process-scheduled')
    ->everyFiveMinutes()
    ->appendOutputTo(storage_path('logs/sheet_process.log'));

Schedule::call(function () {
    \App\Models\User::query()->update(['emails_sent_today' => 0]);
})->dailyAt('00:00');
