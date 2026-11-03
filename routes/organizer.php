<?php

use App\Http\Controllers\Organizer\ApplicationController;
use App\Http\Controllers\Organizer\AssessmentController;
use App\Http\Controllers\Organizer\EventController;
use App\Http\Controllers\Organizer\OrderController;
use App\Http\Controllers\Organizer\OrganizerDashboardController;
use App\Http\Controllers\Organizer\OrganizerProfileController;
use App\Http\Controllers\Organizer\PositionController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'account.active',
    'role:organizer',
])->prefix('organizer')->name('organizer.')->group(function () {

    Route::get('/profile', [OrganizerProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::post('/profile', [OrganizerProfileController::class, 'update'])
        ->name('profile.update');

    Route::get('/profile/pending', [OrganizerProfileController::class, 'pending'])
        ->name('profile.pending');

    Route::get('/profile/documents/{document}/view', [OrganizerProfileController::class, 'viewDocument'])
        ->name('profile.documents.view');

    Route::redirect('/pending', '/organizer/profile/pending');

    Route::middleware('organizer.active')->group(function () {

        Route::get(
            'positions/{position}/assessment',
            [AssessmentController::class, 'edit']
        )->name('assessments.edit');

        Route::put(
            'positions/{position}/assessment',
            [AssessmentController::class, 'update']
        )->name('assessments.update');

        Route::redirect('/dashboard', '/organizer/aktivitas')
            ->name('dashboard');

        Route::get('/aktivitas', [OrganizerDashboardController::class, 'index'])
            ->name('activity.index');

        Route::resource('events', EventController::class);
        Route::put('events/{event}/coordination', [EventController::class, 'updateCoordination'])
            ->name('events.coordination.update');
        Route::post('events/{event}/recruitment-repair', [EventController::class, 'repairRecruitment'])
            ->name('events.recruitment-repair');

        Route::get(
            'events/{event}/text',
            [EventController::class, 'text']
        )->name('events.text');

        Route::patch(
            'events/{event}/text',
            [EventController::class, 'correctText']
        )->name('events.correct-text');

        Route::post(
            'events/{event}/submit',
            [EventController::class, 'submit']
        )->name('events.submit');

        Route::post(
            'events/{event}/publish',
            [EventController::class, 'publish']
        )->name('events.publish');

        Route::post(
            'events/{event}/cancel',
            [EventController::class, 'cancel']
        )->name('events.cancel');

        Route::resource(
            'events.positions',
            PositionController::class
        )->parameters([
            'positions' => 'position',
        ]);

        Route::get(
            'events/{event}/applications',
            [ApplicationController::class, 'index']
        )->name('applications.index');

        Route::get(
            'applications/{application}',
            [ApplicationController::class, 'show']
        )->name('applications.show');

        Route::get(
            'events/{event}/package',
            [EventController::class, 'package']
        )->name('packages.select');

        Route::post(
            'events/{event}/package',
            [EventController::class, 'selectPackage']
        )->name('packages.update');

        Route::post(
            'events/{event}/orders',
            [OrderController::class, 'store']
        )->name('orders.store');

        Route::get(
            'orders/{order}',
            [OrderController::class, 'show']
        )->name('orders.show');

        Route::post(
            'orders/{order}/checkout',
            [OrderController::class, 'checkout']
        )->middleware('throttle:10,1')
            ->name('orders.checkout');

        Route::post(
            'orders/{order}/sync',
            [OrderController::class, 'sync']
        )->middleware('throttle:10,1')
            ->name('orders.sync');
    });
});
