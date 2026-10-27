<?php

use App\Http\Controllers\Volunteer\AssessmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'account.active', 'role:volunteer'])->prefix('volunteer')->name('volunteer.assessments.')->group(function () {
    Route::get('/applications/{application}/assessment', [AssessmentController::class, 'show'])->name('show');
    Route::post('/applications/{application}/assessment', [AssessmentController::class, 'start'])->name('start');
    Route::put('/assessment-attempts/{attempt}/answers', [AssessmentController::class, 'answers'])->name('answers');
    Route::post('/assessment-attempts/{attempt}/submit', [AssessmentController::class, 'submit'])->name('submit');
});
