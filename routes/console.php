<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('mail:process-sheet')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/sheet_process.log'));
