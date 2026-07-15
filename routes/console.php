<?php

use App\Console\Commands\CleanOldEmails;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the automatic email cleanup to run every minute
Schedule::command(CleanOldEmails::class)->everyMinute();
