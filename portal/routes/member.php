<?php

use App\Http\Controllers\Member\BusinessController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\FamilyController;
use App\Http\Controllers\Member\MatrimonialController;
use App\Http\Controllers\Member\MembershipController;
use App\Http\Controllers\Member\PaymentHistoryController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\PrivateImageController;
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

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('family', [FamilyController::class, 'index'])->name('family.index');
        Route::get('family/create', [FamilyController::class, 'create'])->name('family.create');
        Route::post('family', [FamilyController::class, 'store'])->name('family.store');
        Route::get('family/{family}/edit', [FamilyController::class, 'edit'])->name('family.edit');
        Route::put('family/{family}', [FamilyController::class, 'update'])->name('family.update');
        Route::delete('family/{family}', [FamilyController::class, 'destroy'])->name('family.destroy');

        // Business listing (one per member)
        Route::get('business', [BusinessController::class, 'edit'])->name('business');
        Route::put('business', [BusinessController::class, 'update'])->middleware('throttle:10,1')->name('business.update');

        // Private matrimonial (Lifetime Members; others see approved profiles only)
        Route::get('matrimonial', [MatrimonialController::class, 'index'])->name('matrimonial.index');
        Route::post('matrimonial', [MatrimonialController::class, 'store'])->middleware('throttle:10,1')->name('matrimonial.store');
        Route::get('matrimonial/browse', [MatrimonialController::class, 'browse'])->name('matrimonial.browse');
        Route::get('matrimonial/profiles/{profile}', [MatrimonialController::class, 'show'])->name('matrimonial.show');
        Route::post('matrimonial/profiles/{profile}/report', [MatrimonialController::class, 'report'])->middleware('throttle:5,10')->name('matrimonial.report');
        Route::put('matrimonial/{profile}', [MatrimonialController::class, 'update'])->middleware('throttle:10,1')->name('matrimonial.update');
        Route::delete('matrimonial/{profile}', [MatrimonialController::class, 'destroy'])->name('matrimonial.destroy');

        // Lifetime membership (one-time fee) and payment history
        Route::get('membership', [MembershipController::class, 'show'])->name('membership');
        Route::get('membership/pay', [MembershipController::class, 'create'])->name('membership.pay');
        Route::post('membership/pay', [MembershipController::class, 'store'])->middleware('throttle:10,1')->name('membership.pay.store');
        Route::get('payments', [PaymentHistoryController::class, 'index'])->name('payments');
        Route::get('payments/{payment}/receipt', [PaymentHistoryController::class, 'receipt'])->name('payments.receipt');
    });
});

// Private photos (member + family). The controller allows only the owning
// member or an admin with members.view; everyone else gets a 404.
Route::get('photos/member/{member}', [PrivateImageController::class, 'memberPhoto'])->name('photos.member');
Route::get('photos/family/{family}', [PrivateImageController::class, 'familyPhoto'])->name('photos.family');

// Matrimonial photos + bio-data: owner, admin with matrimonial.view, or an
// eligible member viewing an approved profile. Everyone else gets a 404.
Route::get('matrimonial-files/photo/{photo}/{width?}', [MatrimonialController::class, 'photo'])->whereNumber(['photo', 'width'])->name('matrimonial.photo');
Route::get('matrimonial-files/biodata/{profile}', [MatrimonialController::class, 'bioData'])->middleware('throttle:30,1')->name('matrimonial.biodata');
