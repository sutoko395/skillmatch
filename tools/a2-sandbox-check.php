<?php

use App\Models\Event;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Services\PaymentService;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Real Sandbox requests against an explicit demo fixture in the separate test DB.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

try {
    $working = Dotenv::parse(file_get_contents(base_path('.env')));
    $db = DB::connection();
    if (! app()->environment('testing') || app()->configurationIsCached() || $db->getDriverName() !== 'mysql' || $db->getDatabaseName() !== 'skillmatch_testing' || ($working['DB_DATABASE'] ?? '') === 'skillmatch_testing' || $db->getConfig('url')) {
        throw new RuntimeException('Unsafe test target');
    }
    if (filter_var($working['MIDTRANS_IS_PRODUCTION'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
        throw new RuntimeException('Sandbox only');
    }
    config(['midtrans.server_key' => $working['MIDTRANS_SERVER_KEY'] ?? '', 'midtrans.is_production' => false]);
    $args = getopt('', ['checkout', 'sync:', 'env:']);
    $event = Event::where('title', 'Demo A2 - Berbayar approved')->whereHas('organizer', fn ($q) => $q->where('email', 'organizer-a@example.test'))->firstOrFail();
    $service = app(PaymentService::class);
    if (isset($args['checkout'])) {
        $order = $service->createOrder($event, $event->organizer);
        echo 'Local demo order ID: '.$order->id.PHP_EOL;
        $order = $service->checkout($order, $event->organizer);
        if (! $order->checkout_url) {
            throw new RuntimeException('No checkout URL');
        }
        $path = storage_path('app/private/a2-sandbox-checkout.html');
        $url = htmlspecialchars($order->checkout_url, ENT_QUOTES, 'UTF-8');
        file_put_contents($path, '<!doctype html><meta charset="utf-8"><title>SkillMatch Sandbox</title><h1>Sandbox - simulasi pembayaran</h1><p>Fixture database tes. Jangan menggunakan pembayaran nyata.</p><a rel="noreferrer" href="'.$url.'">Buka checkout Midtrans Sandbox</a>');
        echo 'Checkout Sandbox created; local order ID: '.$order->id.PHP_EOL;
        echo 'Open private file: storage/app/private/a2-sandbox-checkout.html'.PHP_EOL;
    } elseif (isset($args['sync'])) {
        $order = Order::where('event_id', $event->id)->findOrFail((int) $args['sync']);
        $order = $service->sync($order);
        echo json_encode(['order_id' => $order->id, 'status' => $order->status, 'paid' => (bool) $order->paid_at, 'activated' => (bool) $order->activated_at, 'receipts' => PaymentEvent::where('order_id', $order->id)->count(), 'entitlements' => $event->entitlement()->count(), 'publication' => $event->fresh()->publication_status], JSON_THROW_ON_ERROR).PHP_EOL;
    } else {
        throw new RuntimeException('Use --checkout or --sync=<local order id> with --env=testing');
    }
} catch (ValidationException $e) {
    echo implode(' ', $e->validator->errors()->all()).PHP_EOL;
    exit(1);
} catch (Throwable $e) {
    // No raw request, response, key, token or exception trace in output/logs.
    echo 'Sandbox check failed ('.get_class($e).'). Check prerequisites; no secret was printed.'.PHP_EOL;
    exit(1);
}
