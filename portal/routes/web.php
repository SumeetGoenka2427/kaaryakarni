<?php

use App\Http\Controllers\Web\Auth\OtpController;
use App\Http\Controllers\Web\Auth\RegistrationController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website routes
|--------------------------------------------------------------------------
| Everything inside Route::localized() exists in English (/about) and Hindi
| (/hi/about). Hindi route names are prefixed with "hi." (hi.about).
*/

Route::localized(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('about', [PageController::class, 'about'])->name('about');
    Route::get('committee', [PageController::class, 'committee'])->name('committee');
    Route::get('membership', [PageController::class, 'membership'])->name('membership');
    Route::get('contact', [ContactController::class, 'show'])->name('contact');
    Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');

    // Member login / registration (mobile + OTP)
    Route::middleware('guest:member')->group(function () {
        Route::get('login', [OtpController::class, 'showLogin'])->name('login');
        Route::get('register', [OtpController::class, 'showRegister'])->name('register');
        Route::post('otp/send', [OtpController::class, 'send'])->middleware('throttle:10,1')->name('otp.send');
        Route::get('otp/verify', [OtpController::class, 'showVerify'])->name('otp.verify');
        Route::post('otp/verify', [OtpController::class, 'verify'])->middleware('throttle:20,1')->name('otp.check');
        Route::post('otp/resend', [OtpController::class, 'resend'])->middleware('throttle:5,1')->name('otp.resend');
        Route::post('otp/restart', [OtpController::class, 'restart'])->name('otp.restart');
        Route::get('register/details', [RegistrationController::class, 'create'])->name('register.details');
        Route::post('register/details', [RegistrationController::class, 'store'])->middleware('throttle:10,1')->name('register.store');
    });

    Route::post('logout', [OtpController::class, 'logout'])->middleware('auth:member')->name('logout');
});
