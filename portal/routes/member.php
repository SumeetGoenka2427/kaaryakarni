<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Member area routes (/member and /hi/member)
|--------------------------------------------------------------------------
| Requires an authenticated member (OTP login, `member` guard) — added in P04.
*/

Route::localized(function () {
    Route::prefix('member')->name('member.')->group(function () {
        //
    });
});
