<?php

use App\Services\Donations\DonationService;
use App\Services\Otp\OtpService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
| Scheduled housekeeping. Production cron: * * * * * php artisan schedule:run
*/

// Old OTP rows (all expired after minutes) are removed daily.
Schedule::call(fn () => app(OtpService::class)->prune())->daily()->name('otp:prune');

// Rebuild donation campaign totals from confirmed donations (safety / repair tool).
Artisan::command('donations:recalculate', function (DonationService $donations) {
    $this->info($donations->recalculate().' campaign total(s) corrected.');
})->purpose('Recalculate donation campaign raised amounts and donor counts');
