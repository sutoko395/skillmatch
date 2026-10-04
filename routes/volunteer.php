<?php
use App\Http\Controllers\Volunteer\VolunteerProfileController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth', 'account.active', 'role:volunteer', 'verified'])->prefix('volunteer')->name('volunteer.')->group(function () {
    Route::redirect('/dashboard', '/volunteer/aktivitas')->name('dashboard');
    Route::view('/aktivitas', 'shared.activity')->name('activity.index');
    Route::get('/profile', [VolunteerProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [VolunteerProfileController::class, 'update'])->name('profile.update');
});
