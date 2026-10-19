<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:generate-recurring-tasks')
    ->everyMinute()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/recurring_tasks.log'));

Schedule::command('app:archive-expired-recurring-tasks')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/archived_tasks.log'));
