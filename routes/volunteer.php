<?php

use App\Http\Controllers\Volunteer\VolunteerApplicationController;
use App\Http\Controllers\Volunteer\VolunteerDashboardController;
use App\Http\Controllers\Volunteer\VolunteerDocumentController;
use App\Http\Controllers\Volunteer\VolunteerProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'account.active',
    'role:volunteer',
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
        )->middleware('verified')->name('applications.store');

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
        )->middleware('verified')->name('applications.submit');

        Route::post(
            '/applications/{application}/documents',
            [VolunteerDocumentController::class, 'store']
        )->middleware(['verified', 'throttle:20,1'])->name('documents.store');

        Route::delete(
            '/documents/{document}',
            [VolunteerDocumentController::class, 'destroy']
        )->middleware('verified')->name('documents.destroy');
    });

require __DIR__.'/volunteer-assessments.php';
