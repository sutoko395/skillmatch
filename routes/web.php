<?php

use App\Http\Controllers\HomeController;
use App\Services\LoginDestination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)
    ->name('home');

Route::get(
    '/dashboard',
    fn (Request $request) => redirect(
        app(LoginDestination::class)->defaultFor($request->user())
    )
)
    ->middleware(['auth', 'account.active'])
    ->name('dashboard');

require __DIR__.'/admin.php';
require __DIR__.'/volunteer.php';
require __DIR__.'/organizer.php';
require __DIR__.'/auth.php';
require __DIR__.'/public.php';