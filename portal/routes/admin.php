<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\CommitteeMemberController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentBlockController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MemberFamilyController;
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

    // Jaipur areas
    Route::middleware('permission:areas.manage,admin')->group(function () {
        Route::get('areas', [AreaController::class, 'index'])->name('areas.index');
        Route::post('areas', [AreaController::class, 'store'])->name('areas.store');
        Route::get('areas/{area}/edit', [AreaController::class, 'edit'])->name('areas.edit');
        Route::put('areas/{area}', [AreaController::class, 'update'])->name('areas.update');
        Route::patch('areas/{area}/toggle', [AreaController::class, 'toggle'])->name('areas.toggle');
        Route::patch('areas/{area}/move', [AreaController::class, 'move'])->name('areas.move');
        Route::delete('areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');
    });

    // Members: view vs. manage permissions
    Route::middleware('permission:members.view,admin')->group(function () {
        Route::get('members', [MemberController::class, 'index'])->name('members.index');
        Route::get('members/export', [MemberController::class, 'export'])->name('members.export');
    });
    Route::middleware('permission:members.manage,admin')->group(function () {
        Route::get('members/create', [MemberController::class, 'create'])->name('members.create');
        Route::post('members', [MemberController::class, 'store'])->name('members.store');
        Route::get('members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('members/{member}', [MemberController::class, 'update'])->name('members.update');
        Route::patch('members/{member}/verify', [MemberController::class, 'verify'])->name('members.verify');
        Route::patch('members/{member}/reject', [MemberController::class, 'reject'])->name('members.reject');
        Route::patch('members/{member}/suspend', [MemberController::class, 'suspend'])->name('members.suspend');
        Route::patch('members/{member}/reinstate', [MemberController::class, 'reinstate'])->name('members.reinstate');

        Route::get('members/{member}/family/create', [MemberFamilyController::class, 'create'])->name('members.family.create');
        Route::post('members/{member}/family', [MemberFamilyController::class, 'store'])->name('members.family.store');
        Route::get('members/{member}/family/{family}/edit', [MemberFamilyController::class, 'edit'])->name('members.family.edit');
        Route::put('members/{member}/family/{family}', [MemberFamilyController::class, 'update'])->name('members.family.update');
        Route::delete('members/{member}/family/{family}', [MemberFamilyController::class, 'destroy'])->name('members.family.destroy');
    });
    Route::get('members/{member}', [MemberController::class, 'show'])->middleware('permission:members.view,admin')->name('members.show');

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
