<?php

use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\EventVerificationController;
use App\Http\Controllers\Admin\OrganizerController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\VolunteerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'account.active', 'role:admin', 'verified', 'can:viewAdmin,App\Models\User'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'admin.foundation')
            ->name('dashboard');

        Route::prefix('master-data')
            ->name('master-data.')
            ->group(function () {
                Route::get('/skills', [SkillController::class, 'index'])
                    ->name('skills.index');

                Route::post('/skills', [SkillController::class, 'store'])
                    ->name('skills.store');

                Route::put('/skills/{skill}', [SkillController::class, 'update'])->can('manage', 'skill')
                    ->name('skills.update');

                Route::delete('/skills/{skill}', [SkillController::class, 'destroy'])->can('manage', 'skill')
                    ->name('skills.destroy');

                Route::get('/event-categories', [EventCategoryController::class, 'index'])
                    ->name('event-categories.index');

                Route::post('/event-categories', [EventCategoryController::class, 'store'])
                    ->name('event-categories.store');

                Route::put('/event-categories/{category}', [EventCategoryController::class, 'update'])->can('manage', 'category')
                    ->name('event-categories.update');

                Route::delete('/event-categories/{category}', [EventCategoryController::class, 'destroy'])->can('manage', 'category')
                    ->name('event-categories.destroy');
            });

        Route::get('/organizers', [OrganizerController::class, 'index'])->name('organizers.index');
        Route::patch('/organizers/{user}/status', [OrganizerController::class, 'updateStatus'])->name('organizers.update-status');
        Route::delete('/organizers/{user}', [OrganizerController::class, 'destroy'])->name('organizers.destroy');

        Route::get('/volunteers', [VolunteerController::class, 'index'])->name('volunteers.index');
        Route::patch('/volunteers/{user}/toggle-status', [VolunteerController::class, 'toggleStatus'])->name('volunteers.toggle-status');
        Route::delete('/volunteers/{user}', [VolunteerController::class, 'destroy'])->name('volunteers.destroy');

        Route::get('/event-verification', [EventVerificationController::class, 'index'])
            ->name('event-verification.index');

        Route::get('/event-verification/{event}', [EventVerificationController::class, 'show'])->can('manage', 'event')
            ->name('event-verification.show');

        Route::patch('/event-verification/{event}/approve', [EventVerificationController::class, 'approve'])->can('manage', 'event')
            ->name('event-verification.approve');

        Route::patch('/event-verification/{event}/reject', [EventVerificationController::class, 'reject'])->can('manage', 'event')
            ->name('event-verification.reject');
    });
