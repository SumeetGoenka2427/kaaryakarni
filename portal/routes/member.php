<?php

use App\Http\Controllers\Member\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Member area routes (/member and /hi/member)
|--------------------------------------------------------------------------
| Logged-in members only (OTP login, `member` guard). Suspended or removed
| accounts are signed out by `member.active`.
*/

Route::localized(function () {
    Route::prefix('member')->name('member.')->middleware(['auth:member', 'member.active'])->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });
});
