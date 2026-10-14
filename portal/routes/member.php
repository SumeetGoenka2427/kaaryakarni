<?php

use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\FamilyController;
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
    });
});

// Private photos (member + family). The controller allows only the owning
// member or an admin with members.view; everyone else gets a 404.
Route::get('photos/member/{member}', [PrivateImageController::class, 'memberPhoto'])->name('photos.member');
Route::get('photos/family/{family}', [PrivateImageController::class, 'familyPhoto'])->name('photos.family');
