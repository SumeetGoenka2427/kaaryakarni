<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SiteTextController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel routes (/admin) — English-only UI, `admin` guard
|--------------------------------------------------------------------------
| Every route below the login requires an active admin session and a
| permission. Authorization is enforced here, not just by hiding menu items.
*/

Route::middleware('guest:admin')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:20,1')->name('login.store');
});

Route::middleware(['auth:admin', 'admin.idle'])->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::middleware('permission:settings.manage,admin')->group(function () {
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('branding', [BrandingController::class, 'edit'])->name('branding.edit');
        Route::post('branding/{type}', [BrandingController::class, 'update'])->name('branding.update');
        Route::delete('branding', [BrandingController::class, 'destroy'])->name('branding.destroy');
    });

    Route::middleware('permission:content.manage,admin')->group(function () {
        Route::get('site-texts', [SiteTextController::class, 'index'])->name('site-texts.index');
        Route::put('site-texts', [SiteTextController::class, 'update'])->name('site-texts.update');
    });
});
