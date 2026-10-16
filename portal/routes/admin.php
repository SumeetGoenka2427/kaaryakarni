<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\BusinessAdminController;
use App\Http\Controllers\Admin\BusinessCategoryController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CommitteeMemberController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentBlockController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonationAdminController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MemberFamilyController;
use App\Http\Controllers\Admin\MatrimonialAdminController;
use App\Http\Controllers\Admin\MembershipAdminController;
use App\Http\Controllers\Admin\PaymentAdminController;
use App\Http\Controllers\Admin\ReceiptAdminController;
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

    // Lifetime memberships
    Route::get('memberships', [MembershipAdminController::class, 'index'])->middleware('permission:memberships.view,admin')->name('memberships.index');
    Route::put('memberships/fee', [MembershipAdminController::class, 'updateFee'])->middleware('permission:memberships.manage,admin')->name('memberships.fee');

    // Business directory
    Route::middleware('permission:businesses.view,admin')->group(function () {
        Route::get('businesses', [BusinessAdminController::class, 'index'])->name('businesses.index');
        Route::get('businesses/{business:id}', [BusinessAdminController::class, 'show'])->whereNumber('business')->name('businesses.show');
    });
    Route::middleware('permission:businesses.manage,admin')->group(function () {
        Route::get('businesses/{business:id}/edit', [BusinessAdminController::class, 'edit'])->name('businesses.edit');
        Route::put('businesses/{business:id}', [BusinessAdminController::class, 'update'])->name('businesses.update');
        Route::patch('businesses/{business:id}/approve', [BusinessAdminController::class, 'approve'])->name('businesses.approve');
        Route::patch('businesses/{business:id}/reject', [BusinessAdminController::class, 'reject'])->name('businesses.reject');
        Route::patch('businesses/{business:id}/changes', [BusinessAdminController::class, 'requestChanges'])->name('businesses.changes');
        Route::patch('businesses/{business:id}/hide', [BusinessAdminController::class, 'hide'])->name('businesses.hide');
        Route::patch('businesses/{business:id}/feature', [BusinessAdminController::class, 'feature'])->name('businesses.feature');
        Route::delete('businesses/{business:id}', [BusinessAdminController::class, 'destroy'])->name('businesses.destroy');

        Route::get('business-categories', [BusinessCategoryController::class, 'index'])->name('categories.index');
        Route::post('business-categories', [BusinessCategoryController::class, 'store'])->name('categories.store');
        Route::put('business-categories/{category}', [BusinessCategoryController::class, 'update'])->name('categories.update');
        Route::patch('business-categories/{category}/toggle', [BusinessCategoryController::class, 'toggle'])->name('categories.toggle');
        Route::delete('business-categories/{category}', [BusinessCategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Matrimonial (private): review + moderation
    Route::middleware('permission:matrimonial.view,admin')->group(function () {
        Route::get('matrimonial', [MatrimonialAdminController::class, 'index'])->name('matrimonial.index');
        Route::get('matrimonial/{profile}', [MatrimonialAdminController::class, 'show'])->name('matrimonial.show');
    });
    Route::middleware('permission:matrimonial.manage,admin')->group(function () {
        Route::get('matrimonial/{profile}/edit', [MatrimonialAdminController::class, 'edit'])->name('matrimonial.edit');
        Route::put('matrimonial/{profile}', [MatrimonialAdminController::class, 'update'])->name('matrimonial.update');
        Route::patch('matrimonial/{profile}/approve', [MatrimonialAdminController::class, 'approve'])->name('matrimonial.approve');
        Route::patch('matrimonial/{profile}/reject', [MatrimonialAdminController::class, 'reject'])->name('matrimonial.reject');
        Route::patch('matrimonial/{profile}/hide', [MatrimonialAdminController::class, 'hide'])->name('matrimonial.hide');
        Route::patch('matrimonial/{profile}/suspend', [MatrimonialAdminController::class, 'suspend'])->name('matrimonial.suspend');
        Route::delete('matrimonial/{profile}', [MatrimonialAdminController::class, 'destroy'])->name('matrimonial.destroy');
        Route::patch('matrimonial-reports/{report}/resolve', [MatrimonialAdminController::class, 'resolveReport'])->name('matrimonial.reports.resolve');
    });

    // Donation campaigns + records
    Route::middleware('permission:donations.view,admin')->group(function () {
        Route::get('campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
        Route::get('donations', [DonationAdminController::class, 'index'])->name('donations.index');
        Route::get('donations/export', [DonationAdminController::class, 'export'])->name('donations.export');
    });
    Route::middleware('permission:donations.manage,admin')->group(function () {
        Route::get('campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
        Route::get('campaigns/{campaign:id}/edit', [CampaignController::class, 'edit'])->name('campaigns.edit');
        Route::put('campaigns/{campaign:id}', [CampaignController::class, 'update'])->name('campaigns.update');
        Route::patch('campaigns/{campaign:id}/toggle', [CampaignController::class, 'toggle'])->name('campaigns.toggle');
        Route::delete('campaigns/{campaign:id}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
        Route::patch('donations/{donation}/refund', [DonationAdminController::class, 'refund'])->name('donations.refund');
    });

    // Payments & receipts
    Route::middleware('permission:payments.view,admin')->group(function () {
        Route::get('payments', [PaymentAdminController::class, 'index'])->name('payments.index');
        Route::get('payments/export', [PaymentAdminController::class, 'export'])->name('payments.export');
        Route::get('payments/{payment}', [PaymentAdminController::class, 'show'])->name('payments.show');
        Route::get('payments/{payment}/proof', [PaymentAdminController::class, 'proof'])->name('payments.proof');
        Route::get('receipts', [ReceiptAdminController::class, 'index'])->name('receipts.index');
        Route::get('receipts/{receipt}/download', [ReceiptAdminController::class, 'download'])->name('receipts.download');
    });
    Route::middleware('permission:payments.verify,admin')->group(function () {
        Route::patch('payments/{payment}/verify', [PaymentAdminController::class, 'verify'])->name('payments.verify');
        Route::patch('payments/{payment}/reject', [PaymentAdminController::class, 'reject'])->name('payments.reject');
        Route::patch('receipts/{receipt}/regenerate', [ReceiptAdminController::class, 'regenerate'])->name('receipts.regenerate');
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

        // Gallery albums + photos
        Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
        Route::get('gallery/create', [GalleryController::class, 'create'])->name('gallery.create');
        Route::post('gallery', [GalleryController::class, 'store'])->name('gallery.store');
        Route::get('gallery/{album:id}/edit', [GalleryController::class, 'edit'])->name('gallery.edit');
        Route::put('gallery/{album:id}', [GalleryController::class, 'update'])->name('gallery.update');
        Route::patch('gallery/{album:id}/toggle', [GalleryController::class, 'toggle'])->name('gallery.toggle');
        Route::delete('gallery/{album:id}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
        Route::post('gallery/{album:id}/photos', [GalleryController::class, 'upload'])->name('gallery.upload');
        Route::put('gallery-photos/{image}', [GalleryController::class, 'updateImage'])->name('gallery.images.update');
        Route::patch('gallery-photos/{image}/move', [GalleryController::class, 'moveImage'])->name('gallery.images.move');
        Route::patch('gallery-photos/{image}/cover', [GalleryController::class, 'coverImage'])->name('gallery.images.cover');
        Route::delete('gallery-photos/{image}', [GalleryController::class, 'destroyImage'])->name('gallery.images.destroy');

        Route::resource('committee', CommitteeMemberController::class)->except('show');
        Route::resource('announcements', AnnouncementController::class)->except('show');

        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::patch('contact-messages/{contactMessage}/resolve', [ContactMessageController::class, 'resolve'])->name('contact-messages.resolve');
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
    });
});
