<?php

use App\Http\Controllers\Organizer\EventController;
use App\Http\Controllers\Organizer\OrderController;
use App\Http\Controllers\Organizer\PositionController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'account.active',
    'role:organizer',
])->prefix('organizer')->name('organizer.')->group(function () {

    Route::get('/profile', [\App\Http\Controllers\Organizer\OrganizerProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::post('/profile', [\App\Http\Controllers\Organizer\OrganizerProfileController::class, 'update'])
        ->name('profile.update');

    Route::get('/profile/pending', [\App\Http\Controllers\Organizer\OrganizerProfileController::class, 'pending'])
        ->name('profile.pending');

    Route::get('/profile/documents/{document}/view', [\App\Http\Controllers\Organizer\OrganizerProfileController::class, 'viewDocument'])
        ->name('profile.documents.view');

    Route::redirect('/pending', '/organizer/profile/pending');

    Route::middleware('organizer.active')->group(function () {

        Route::redirect('/dashboard', '/organizer/aktivitas')
            ->name('dashboard');

        Route::get('/aktivitas', [\App\Http\Controllers\Organizer\OrganizerDashboardController::class, 'index'])
            ->name('activity.index');

        Route::resource('events', EventController::class);

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