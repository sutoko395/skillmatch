<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Route Dashboard Utama (Pengecekan Role Aman)
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user && isset($user->role) && $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard');
    })->name('dashboard');

    // Route Dashboard Admin
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Breeze Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // SkillMatch Custom Profile Routes
    Route::get('/profile/setup', [ProfileController::class, 'editProfile'])->name('profile.setup');
    Route::post('/profile/volunteer', [ProfileController::class, 'updateVolunteerProfile'])->name('profile.volunteer.update');
    Route::post('/profile/organizer', [ProfileController::class, 'updateOrganizerProfile'])->name('profile.organizer.update');

    // Admin Master Data Routes
    Route::get('/admin/master-data', [MasterDataController::class, 'index'])->name('admin.master-data');
    Route::post('/admin/skills', [MasterDataController::class, 'storeSkill'])->name('admin.skills.store');
    Route::delete('/admin/skills/{skill}', [MasterDataController::class, 'destroySkill'])->name('admin.skills.destroy');
    Route::post('/admin/categories', [MasterDataController::class, 'storeCategory'])->name('admin.categories.store');
    Route::delete('/admin/categories/{category}', [MasterDataController::class, 'destroyCategory'])->name('admin.categories.destroy');
});

require __DIR__.'/auth.php';