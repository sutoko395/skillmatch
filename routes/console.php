<?php

use App\Services\A2NotificationReplay;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('a2:retry-notifications', function () {
    $result = app(A2NotificationReplay::class)->run();
    $this->info('Enqueued/deduplicated: '.$result['sent'].'; pending A4 integration: '.$result['pending']);
})->purpose('Replay A2 business notifications through the A4 outbox with stable dedupe keys');
