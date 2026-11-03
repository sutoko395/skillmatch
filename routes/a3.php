<?php

use App\Http\Controllers\Organizer\ExecutionController;
use App\Http\Controllers\Volunteer\HistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'account.active', 'role:organizer', 'organizer.active'])
    ->prefix('organizer')->name('organizer.')->group(function () {
        Route::post('applications/{application}/accept', [ExecutionController::class, 'accept'])->name('applications.accept');
        Route::post('applications/{application}/reject', [ExecutionController::class, 'reject'])->name('applications.reject');
        Route::post('applications/{application}/withdraw', [ExecutionController::class, 'withdraw'])->name('applications.withdraw');
        Route::post('events/{event}/close-selection', [ExecutionController::class, 'close'])->name('events.close-selection');
        Route::get('events/{event}/execution', [ExecutionController::class, 'show'])->name('execution.show');
        Route::patch('attendances/{attendance}', [ExecutionController::class, 'record'])->name('attendances.update');
        Route::post('attendances/{attendance}/sessions', [ExecutionController::class, 'prepareSessions'])->name('attendances.prepare-sessions');
        Route::post('applications/{application}/attendance-sessions', [ExecutionController::class, 'prepareApplicationSessions'])->name('applications.prepare-attendance-sessions');
        Route::patch('attendance-sessions/{session}', [ExecutionController::class, 'recordSession'])->name('attendance-sessions.update');
        Route::post('events/{event}/complete', [ExecutionController::class, 'complete'])->name('events.complete');
    });

Route::middleware(['auth', 'account.active', 'verified', 'role:volunteer'])
    ->prefix('volunteer')->name('volunteer.')->group(function () {
        Route::post('applications/{application}/withdraw', [ExecutionController::class, 'withdraw'])->name('applications.withdraw');
        Route::get('history', [HistoryController::class, 'index'])->name('history.index');
    });
