<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Volunteer\VolunteerDashboardController;
use App\Http\Controllers\Volunteer\VolunteerProfileController;
use App\Http\Controllers\Admin\EventVerificationController;
use App\Http\Controllers\Admin\OrganizerController;
use App\Http\Controllers\Admin\VolunteerController;
use App\Http\Controllers\Organizer\OrganizerDashboardController;
use App\Http\Controllers\Organizer\OrganizerProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'volunteer') {
            return redirect()->route('volunteer.dashboard');
        }

        if ($user->role === 'organizer') {
            if ($user->organizer_status === 'active') {
                return redirect()->route('organizer.dashboard');
            }

            return redirect()->route('organizer.profile.edit');
        }

        abort(403);
    })->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::prefix('master-data')
            ->name('master-data.')
            ->group(function () {
                Route::get('/skills', [SkillController::class, 'index'])
                    ->name('skills.index');

                Route::post('/skills', [SkillController::class, 'store'])
                    ->name('skills.store');

                Route::put('/skills/{skill}', [SkillController::class, 'update'])
                    ->name('skills.update');

                Route::delete('/skills/{skill}', [SkillController::class, 'destroy'])
                    ->name('skills.destroy');

                Route::get('/event-categories', [EventCategoryController::class, 'index'])
                    ->name('event-categories.index');

                Route::post('/event-categories', [EventCategoryController::class, 'store'])
                    ->name('event-categories.store');

                Route::put('/event-categories/{category}', [EventCategoryController::class, 'update'])
                    ->name('event-categories.update');

                Route::delete('/event-categories/{category}', [EventCategoryController::class, 'destroy'])
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

        Route::get('/event-verification/{event}', [EventVerificationController::class, 'show'])
            ->name('event-verification.show');

        Route::patch('/event-verification/{event}/approve', [EventVerificationController::class, 'approve'])
            ->name('event-verification.approve');

        Route::patch('/event-verification/{event}/reject', [EventVerificationController::class, 'reject'])
            ->name('event-verification.reject');
    });

Route::middleware(['auth', 'verified', 'role:volunteer'])
    ->prefix('volunteer')
    ->name('volunteer.')
    ->group(function () {
        Route::get('/dashboard', [VolunteerDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/profile', [VolunteerProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::post('/profile', [VolunteerProfileController::class, 'update'])
            ->name('profile.update');
    });

Route::middleware(['auth', 'verified', 'role:organizer'])
    ->prefix('organizer')
    ->name('organizer.')
    ->group(function () {
        Route::get('/profile', [OrganizerProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::post('/profile', [OrganizerProfileController::class, 'update'])
            ->name('profile.update');

        Route::get('/pending', [OrganizerProfileController::class, 'pending'])
            ->name('profile.pending');

        Route::middleware('organizer.active')->group(function () {
            Route::get('/dashboard', [OrganizerDashboardController::class, 'index'])
                ->name('dashboard');
        });
    });

require __DIR__.'/auth.php';