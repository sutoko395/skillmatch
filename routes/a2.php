<?php

use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Organizer\EventController;
use App\Http\Controllers\Organizer\OrderController;
use App\Http\Controllers\Organizer\PositionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'account.active', 'role:organizer', 'verified', 'organizer.active'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::resource('events', EventController::class);
    Route::get('events/{event}/text', [EventController::class, 'text'])->name('events.text');
    Route::patch('events/{event}/text', [EventController::class, 'correctText'])->name('events.correct-text');
    Route::post('events/{event}/submit', [EventController::class, 'submit'])->name('events.submit');
    Route::post('events/{event}/publish', [EventController::class, 'publish'])->name('events.publish');
    Route::post('events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');
    Route::resource('events.positions', PositionController::class)->parameters(['positions' => 'position']);
    Route::get('events/{event}/package', [EventController::class, 'package'])->name('packages.select');
    Route::post('events/{event}/package', [EventController::class, 'selectPackage'])->name('packages.update');
    Route::post('events/{event}/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/checkout', [OrderController::class, 'checkout'])->middleware('throttle:10,1')->name('orders.checkout');
    Route::post('orders/{order}/sync', [OrderController::class, 'sync'])->middleware('throttle:10,1')->name('orders.sync');
});
Route::middleware(['auth', 'account.active', 'role:admin', 'verified', 'can:viewAdmin,App\Models\User'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('packages', PackageController::class)->except(['show', 'destroy']);
    Route::get('orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/sync', [App\Http\Controllers\Admin\OrderController::class, 'sync'])->middleware('throttle:10,1')->name('orders.sync');
});
