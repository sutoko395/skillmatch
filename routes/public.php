<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Middleware\EnsureAccountActive;
use App\Models\Event;
use Illuminate\Support\Facades\Route;

Route::get('/events', [CatalogController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [CatalogController::class, 'show'])->name('events.show');
Route::post('/payments/midtrans/notification', MidtransNotificationController::class)->withoutMiddleware([EnsureAccountActive::class])->middleware('throttle:120,1')->name('payments.midtrans.notification');

Route::get('/events/{event}/join', function (Event $event) {
    abort_unless(Event::publiclyVisible()->whereKey($event->id)->exists(), 404);

    return redirect()->route('events.show', $event);
})->middleware(['auth', 'account.active', 'role:volunteer', 'verified'])->name('events.join');
