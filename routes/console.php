<?php

use App\Services\PseudonymService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cleanup expired pseudonyms every 5 minutes
Schedule::call(fn() => app(PseudonymService::class)->cleanup())
    ->everyFiveMinutes()
    ->name('pseudonym-cleanup');
