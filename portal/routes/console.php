<?php

use App\Services\Otp\OtpService;
use Illuminate\Support\Facades\Schedule;

/*
| Scheduled housekeeping. Production cron: * * * * * php artisan schedule:run
*/

// Old OTP rows (all expired after minutes) are removed daily.
Schedule::call(fn () => app(OtpService::class)->prune())->daily()->name('otp:prune');
