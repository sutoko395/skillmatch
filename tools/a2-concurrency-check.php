<?php

use App\Contracts\AssessmentReadiness;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Services\EntitlementService;
use App\Services\EventPublicationService;
use App\Services\PaymentService;
use App\Services\VerifiedGatewayResult;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;
use Tests\Support\A2Fixture;

// Synthetic integration fixtures only, permanently guarded to a separate MySQL test database.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

try {
    $db = DB::connection();
    $working = Dotenv::parse(file_get_contents(base_path('.env')));
    if (! app()->environment('testing') || app()->configurationIsCached() || $db->getDriverName() !== 'mysql' || $db->getDatabaseName() !== 'skillmatch_testing' || ($working['DB_DATABASE'] ?? '') === 'skillmatch_testing' || $db->getConfig('url')) {
        throw new RuntimeException('Unsafe test target');
    }
    $args = getopt('', ['worker:', 'event:', 'order:', 'go:', 'env:']);
    if (isset($args['worker'])) {
        while (microtime(true) < (float) $args['go']) {
            usleep(10000);
        }
        if ($args['worker'] === 'create') {
            $event = Event::findOrFail($args['event']);
            app(PaymentService::class)->createOrder($event, $event->organizer);
        } elseif ($args['worker'] === 'reconcile') {
            $order = Order::findOrFail($args['order']);
            app(PaymentService::class)->reconcile($order, new VerifiedGatewayResult($order->order_ref, 'concurrency-test-'.$order->id, $order->amount, 'IDR', 'settlement', 'accept', 'paid'));
        } elseif ($args['worker'] === 'publish') {
            app()->bind(AssessmentReadiness::class, fn () => new class implements AssessmentReadiness
            {
                public function publishedVersion(EventPosition $position): int
                {
                    return 1;
                }
            });
            $event = Event::findOrFail($args['event']);
            app(EventPublicationService::class)->publish($event, $event->organizer);
        } else {
            try {
                DB::transaction(fn () => app(EntitlementService::class)->consumeApplication(Event::findOrFail($args['event'])), 3);
                echo 'accepted';
            } catch (ValidationException) {
                echo 'limit';
            }
        }
        exit(0);
    }
    $fixture = new class
    {
        use A2Fixture;

        public $app;

        public function prepare()
        {
            $this->app = app();
            $this->assessmentReady();

            return $this->fixture(10000);
        }
    };
    [$owner,$event] = $fixture->prepare();
    $event->forceFill(['status' => 'approved'])->save();
    $cfg = $db->getConfig();
    $env = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'skillmatch_testing', 'DB_URL' => '', 'DB_HOST' => $cfg['host'], 'DB_PORT' => (string) $cfg['port'], 'DB_USERNAME' => $cfg['username'], 'DB_PASSWORD' => (string) $cfg['password']];
    $race = function ($mode, $id) use ($env) {
        $go = (string) (microtime(true) + 1.5);
        $processes = [];
        for ($i = 0; $i < 2; $i++) {
            $p = new Process([PHP_BINARY, __FILE__, '--env=testing', '--worker='.$mode, ($mode === 'reconcile' ? '--order=' : '--event=').$id, '--go='.$go], base_path(), $env);
            $p->setTimeout(30);
            $p->start();
            $processes[] = $p;
        }
        $outputs = [];
        foreach ($processes as $p) {
            $p->wait();
            if (! $p->isSuccessful()) {
                preg_match('/Concurrency verification failed: ([^\r\n]+)/', $p->getOutput(), $diagnostic);
                throw new RuntimeException('Concurrent worker '.$mode.' failed: '.($diagnostic[1] ?? 'bootstrap failure'));
            } $outputs[] = trim($p->getOutput());
        }

        return $outputs;
    };
    $race('create', $event->id);
    if ($event->orders()->count() !== 1) {
        throw new RuntimeException('Duplicate order');
    }
    $order = $event->orders()->first();
    $race('reconcile', $order->id);
    if ($event->entitlement()->count() !== 1 || PaymentEvent::where('order_id', $order->id)->count() !== 1 || ! $order->fresh()->paid_at || ! $order->fresh()->activated_at) {
        throw new RuntimeException('Duplicate activation/receipt');
    }
    $race('publish', $event->id);
    if (AuditLog::where('subject_id', $event->id)->where('action', 'event.published')->count() !== 1 || ! $event->fresh()->published_at) {
        throw new RuntimeException('Publication race failed');
    }
    $results = $race('consume', $event->id);
    sort($results);
    if ($results !== ['accepted', 'limit'] || $event->entitlement()->first()->submitted_applications !== 1) {
        throw new RuntimeException('Quota race failed');
    }
    echo "PASS: two concurrent order requests -> one order.\nPASS: two payment reconciliations -> one receipt/entitlement.\nPASS: two publications -> one timestamp/audit.\nPASS: two application slot reservations -> one success, one limit.\n";
    echo "Synthetic fixtures retained in skillmatch_testing; audit history is append-only.\n";
} catch (Throwable $e) {
    echo 'Concurrency verification failed: '.get_class($e).(get_class($e) === RuntimeException::class ? ': '.$e->getMessage() : '; details suppressed')."\n";
    exit(1);
}
