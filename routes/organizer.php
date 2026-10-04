<?php
use App\Http\Controllers\Organizer\OrganizerProfileController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth', 'account.active', 'role:organizer'])->prefix('organizer')->name('organizer.')->group(function () {
    // Basic profile remains accessible while email/organization verification is pending.
    Route::get('/profile', [OrganizerProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [OrganizerProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/pending', [OrganizerProfileController::class, 'pending'])->name('profile.pending');
    Route::redirect('/pending', '/organizer/profile/pending');
    Route::middleware(['verified', 'organizer.active'])->group(function () {
        Route::redirect('/dashboard', '/organizer/aktivitas')->name('dashboard');
        Route::view('/aktivitas', 'shared.activity')->name('activity.index');
    });
});
