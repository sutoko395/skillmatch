<?php

use App\Http\Controllers\Volunteer\VolunteerApplicationController;
use App\Http\Controllers\Volunteer\VolunteerDashboardController;
use App\Http\Controllers\Volunteer\VolunteerProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'account.active',
    'role:volunteer',
    'verified',
])
    ->prefix('volunteer')
    ->name('volunteer.')
    ->group(function () {

        Route::redirect('/dashboard', '/volunteer/aktivitas')
            ->name('dashboard');

        Route::get(
            '/aktivitas',
            [VolunteerDashboardController::class, 'index']
        )->name('activity.index');

        Route::get(
            '/profile',
            [VolunteerProfileController::class, 'edit']
        )->name('profile.edit');

        Route::post(
            '/profile',
            [VolunteerProfileController::class, 'update']
        )->name('profile.update');

        Route::post(
            '/positions/{position}/applications',
            [VolunteerApplicationController::class, 'store']
        )->name('applications.store');

        Route::get(
            '/applications',
            [VolunteerApplicationController::class, 'index']
        )->name('applications.index');

        Route::get(
            '/applications/{application}',
            [VolunteerApplicationController::class, 'show']
        )->name('applications.show');

        Route::post(
            '/applications/{application}/submit',
            [VolunteerApplicationController::class, 'submit']
        )->name('applications.submit');
    });