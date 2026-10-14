<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\CommitteeMemberController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentBlockController;
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

    Route::middleware('permission:admins.manage,admin')->group(function () {
        Route::resource('admin-users', AdminUserController::class)->except(['show', 'destroy'])
            ->parameters(['admin-users' => 'adminUser']);
    });

    Route::middleware('permission:content.manage,admin')->group(function () {
        Route::get('site-texts', [SiteTextController::class, 'index'])->name('site-texts.index');
        Route::put('site-texts', [SiteTextController::class, 'update'])->name('site-texts.update');

        // Page Content (About / Membership / Homepage blocks)
        Route::get('content', [ContentBlockController::class, 'index'])->name('content.index');
        Route::get('content/{page}/{section}/create', [ContentBlockController::class, 'create'])->name('content.create');
        Route::post('content/{page}/{section}', [ContentBlockController::class, 'store'])->name('content.store');
        Route::get('content/{block}/edit', [ContentBlockController::class, 'edit'])->name('content.edit');
        Route::put('content/{block}', [ContentBlockController::class, 'update'])->name('content.update');
        Route::delete('content/{block}', [ContentBlockController::class, 'destroy'])->name('content.destroy');

        // Homepage top banner: carousel slides + left/right panels
        Route::resource('banners', BannerController::class)->except('show');
        Route::patch('banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
        Route::put('banner-panels/{position}', [BannerController::class, 'updatePanel'])->name('banner-panels.update');

        Route::resource('committee', CommitteeMemberController::class)->except('show');
        Route::resource('announcements', AnnouncementController::class)->except('show');

        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::patch('contact-messages/{contactMessage}/resolve', [ContactMessageController::class, 'resolve'])->name('contact-messages.resolve');
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
    });
});
