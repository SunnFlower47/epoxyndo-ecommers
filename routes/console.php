<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Jalankan queue worker secara otomatis tiap menit (berguna untuk shared hosting)
Schedule::command('queue:work --max-time=50 --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();
