<?php
use App\Services\LoginDestination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::view('/', 'welcome')->name('home');
Route::get('/dashboard', fn (Request $request) => redirect(app(LoginDestination::class)->defaultFor($request->user())))
    ->middleware(['auth', 'account.active'])->name('dashboard');
require __DIR__.'/admin.php';
require __DIR__.'/volunteer.php';
require __DIR__.'/organizer.php';
require __DIR__.'/auth.php';
