<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// every 28th of each month at 23:59
Schedule::command('correction:apply-monthly')
    ->monthlyOn(28, '23:59')
    ->name('monthly_correction')
    ->description('Aplica correção monetária mensal em todas as contas')
    ->withoutOverlapping()
    ->runInBackground();
