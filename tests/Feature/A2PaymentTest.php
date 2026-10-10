<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\User;
use App\Services\A2NotificationReplay;
use App\Services\EventPublicationService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use App\Services\VerifiedGatewayResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class A2PaymentTest extends DatabaseTestCase
{
    use A2Fixture;

    private function order(): array
    {
        [$owner,$event,,$package] = $this->fixture(10000);
        $event->forceFill(['status' => 'approved'])->save();

        return [$owner, $event, app(PaymentService::class)->createOrder($event, $owner), $package];
    }

    private function response(Order $order, string $status = 'settlement'): array
    {
        return ['order_id' => $order->order_ref, 'transaction_id' => 'test-transaction-'.$order->id, 'gross_amount' => '10000.00', 'currency' => 'IDR', 'transaction_status' => $status, 'fraud_status' => 'accept', 'status_code' => '200'];
    }

    public function test_duplicate_order_and_checkout_return_do_not_mark_paid(): void
    {
        [$owner,$event,$order] = $this->order();
        $again = app(PaymentService::class)->createOrder($event, $owner);
        $this->assertSame($order->id, $again->id);
        $this->actingAs($owner)->get("/organizer/orders/$order->id?transaction_status=settlement&status=paid")->assertOk();
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, $event->entitlement()->count());
        $this->actingAs(User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']))->get("/organizer/orders/$order->id")->assertForbidden();
    }

    public function test_verified_status_is_idempotent_and_late_pending_does_not_downgrade(): void
    {
        [$owner,$event,$order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fakeSequence('api.sandbox.midtrans.com/*')->push($this->response($order))->push($this->response($order))->push($this->response($order, 'pending'));
        $service = app(PaymentService::class);
        $first = $service->sync($order);
        $second = $service->sync($order);
        $this->assertSame('paid', $second->status);
        $this->assertTrue($first->paid_at->equalTo($second->paid_at));
        $this->assertTrue($first->activated_at->equalTo($second->activated_at));
        $this->assertSame(1, PaymentEvent::where('order_id', $order->id)->count());
        $this->assertSame(1, $event->entitlement()->count());
        $this->assertSame('paid', $service->sync($order)->status);
        $this->assertSame('unpublished', $event->fresh()->publication_status); // Missing A4 blocks publication, not payment history.
    }

    public function test_signature_invalid_and_signed_forged_status_cannot_pay_order(): void
    {
        [$owner,$event,$order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::preventStrayRequests();
        $input = $this->response($order);
        $input['signature_key'] = 'invalid';
        $this->postJson('/payments/midtrans/notification', $input)->assertForbidden();
        Http::assertNothingSent();
        $input['signature_key'] = hash('sha512', $order->order_ref.'200'.'10000.00'.'test-key');
        // Valid signature cannot authenticate transaction_status: authoritative API remains pending.
        Http::fake(['api.sandbox.midtrans.com/*' => Http::response($this->response($order, 'pending'))]);
        $this->postJson('/payments/midtrans/notification', $input)->assertOk();
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->paid_at);
    }

    public function test_wrong_amount_currency_or_order_reference_is_rejected(): void
    {
        [$owner,$event,$order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::preventStrayRequests();
        $this->actingAs($owner);
        foreach (['gross_amount' => '9000.00', 'currency' => 'USD', 'order_id' => 'foreign-order'] as $key => $wrong) {
            Http::swap(new Factory);
            Http::preventStrayRequests();
            Http::fake(['api.sandbox.midtrans.com/*' => Http::response(array_replace($this->response($order), [$key => $wrong]))]);
            $this->postJson("/organizer/orders/$order->id/sync")->assertUnprocessable();
            $this->assertSame('pending', $order->fresh()->status);
        }
        $this->assertSame(0, PaymentEvent::where('order_id', $order->id)->count());
    }

    public function test_snap_without_selected_payment_method_keeps_order_pending(): void
    {
        [$owner, $event, $order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake(['api.sandbox.midtrans.com/*' => Http::response(['status_code' => '404', 'status_message' => 'Transaction does not exist.'], 200)]);
        $this->actingAs($owner)->postJson("/organizer/orders/$order->id/sync")
            ->assertUnprocessable()->assertJsonValidationErrors('payment');
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, $event->entitlement()->count());
        $this->assertSame(0, PaymentEvent::where('order_id', $order->id)->count());
    }

    public function test_cancelled_event_receives_payment_history_without_entitlement(): void
    {
        [$owner,$event,$order] = $this->order();
        $event->forceFill(['lifecycle_status' => 'cancelled', 'cancelled_at' => now()])->save();
        config(['midtrans.server_key' => 'test-key']);
        Http::fake(['api.sandbox.midtrans.com/*' => Http::response($this->response($order))]);
        $paid = app(PaymentService::class)->sync($order);
        $this->assertSame('paid', $paid->status);
        $this->assertNotNull($paid->paid_at);
        $this->assertTrue($paid->requires_follow_up);
        $this->assertNull($paid->activated_at);
        $this->assertSame(0, $event->entitlement()->count());
        $this->assertSame('unpublished', $event->fresh()->publication_status);
    }

    public function test_checkout_uses_server_snapshot_and_http_runs_without_its_own_transaction(): void
    {
        [$owner,$event,$order,$package] = $this->order();
        $package->update(['price' => 99999, 'is_active' => false]);
        config(['midtrans.server_key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake(function ($request) use ($order) {
            // PHPUnit outer transaction is the only transaction; checkout must release its own locks.
            $this->assertSame(1, DB::transactionLevel());
            $this->assertSame(10000, $request['transaction_details']['gross_amount']);
            $this->assertSame($order->order_ref, $request['transaction_details']['order_id']);
            $this->assertSame(route('organizer.orders.show', ['order' => $order, 'check_payment' => 1]), $request['callbacks']['finish']);

            return Http::response(['redirect_url' => 'https://app.sandbox.midtrans.com/snap/v3/redirection/test-token']);
        });
        $this->actingAs($owner)->post("/organizer/orders/$order->id/checkout", ['amount' => 1])->assertRedirect();
        $this->assertSame(10000, $order->fresh()->amount);
        $this->get("/organizer/orders/$order->id")->assertSee('Buka pembayaran Sandbox');
        $this->post("/organizer/orders/$order->id/checkout")->assertRedirect();
        Http::assertSentCount(1);
        $raw = DB::table('orders')->where('id', $order->id)->value('checkout_url');
        $this->assertStringNotContainsString('test-token', $raw);
    }

    public function test_fraud_challenge_requires_review_and_admin_cannot_mark_paid(): void
    {
        [$owner,$event,$order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::fake(['api.sandbox.midtrans.com/*' => Http::response(array_replace($this->response($order, 'capture'), ['fraud_status' => 'challenge']))]);
        $review = app(PaymentService::class)->sync($order);
        $this->assertSame('review_required', $review->status);
        $this->assertNull($review->paid_at);
        $this->assertSame(0, $event->entitlement()->count());
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get("/admin/orders/$order->id")->assertOk();
        $this->patchJson("/admin/orders/$order->id", ['status' => 'paid'])->assertMethodNotAllowed();
    }

    public function test_gateway_failure_does_not_change_order_or_leak_response(): void
    {
        [$owner,$event,$order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::fake(['api.sandbox.midtrans.com/*' => Http::response(['secret' => 'do-not-display'], 500)]);
        $this->actingAs($owner)->postJson("/organizer/orders/$order->id/sync")->assertUnprocessable()->assertDontSee('do-not-display');
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_successful_payment_publishes_when_assessment_integration_is_ready(): void
    {
        $this->assessmentReady();
        [$owner,$event,$order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::fake(['api.sandbox.midtrans.com/*' => Http::response($this->response($order))]);
        app(PaymentService::class)->sync($order);
        $this->assertSame('published', $event->fresh()->publication_status);
    }

    public function test_notification_failure_rolls_back_financial_change_and_audit(): void
    {
        [$owner,$event,$order] = $this->order();
        $notifications = \Mockery::mock();
        $notifications->shouldReceive('enqueue')->andThrow(new \RuntimeException('Test outbox unavailable'));
        $this->app->instance(NotificationService::class, $notifications);
        $before = AuditLog::count();
        try {
            app(PaymentService::class)->reconcile($order, new VerifiedGatewayResult($order->order_ref, 'test-rollback', 10000, 'IDR', 'settlement', 'accept', 'paid'));
            $this->fail('Expected transaction rollback');
        } catch (\RuntimeException $e) {
            $this->assertSame('Test outbox unavailable', $e->getMessage());
        }
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, $event->entitlement()->count());
        $this->assertSame(0, PaymentEvent::where('order_id', $order->id)->count());
        $this->assertSame($before, AuditLog::count());
    }

    public function test_notification_replay_uses_stable_keys_and_marks_pending_receipt(): void
    {
        [$owner,$event,$order] = $this->order();
        app(PaymentService::class)->reconcile($order, new VerifiedGatewayResult($order->order_ref, 'test-replay', 10000, 'IDR', 'settlement', 'accept', 'paid'));
        $receipt = PaymentEvent::where('order_id', $order->id)->firstOrFail();
        $this->assertNull($receipt->notified_at);
        $calls = [];
        $notifications = new class($calls)
        {
            public array $keys = [];

            public function __construct($unused) {}

            public function enqueue($key, $user, $payload)
            {
                $this->keys[] = $key;
            }
        };
        $this->app->instance(NotificationService::class, $notifications);
        app(A2NotificationReplay::class)->run();
        $expected = 'payment.verified:'.$receipt->id.':1:'.$owner->id;
        $this->assertContains($expected, $notifications->keys);
        $this->assertNotNull($receipt->fresh()->notified_at);
        $notifications->keys = [];
        app(A2NotificationReplay::class)->run();
        $this->assertNotContains($expected, $notifications->keys);
    }

    public function test_refund_requires_review_and_cannot_be_resumed_by_admin(): void
    {
        $this->assessmentReady();
        [$owner,$event,$order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::fakeSequence('api.sandbox.midtrans.com/*')->push($this->response($order))->push($this->response($order, 'refund'));
        app(PaymentService::class)->sync($order);
        $paidAt = $order->fresh()->paid_at;
        app(PaymentService::class)->sync($order);
        $this->assertSame('review_required', $order->fresh()->status);
        $this->assertTrue($paidAt->equalTo($order->fresh()->paid_at));
        $this->assertSame('suspended', $event->fresh()->publication_status);
        $this->get('/events/'.$event->id)->assertNotFound();
        $admin = User::factory()->create(['role' => 'admin']);
        try {
            app(EventPublicationService::class)->resume($event, $admin);
            $this->fail('Payment review must prevent resume');
        } catch (ValidationException) {
            $this->assertSame('suspended', $event->fresh()->publication_status);
        }
    }

    public function test_challenge_resolved_by_gateway_can_activate_without_manual_paid_edit(): void
    {
        [$owner,$event,$order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::fakeSequence('api.sandbox.midtrans.com/*')->push(array_replace($this->response($order, 'capture'), ['fraud_status' => 'challenge']))->push($this->response($order));
        app(PaymentService::class)->sync($order);
        $this->assertTrue($order->fresh()->requires_follow_up);
        $paid = app(PaymentService::class)->sync($order);
        $this->assertSame('paid', $paid->status);
        $this->assertFalse($paid->requires_follow_up);
        $this->assertNotNull($paid->activated_at);
        $this->assertSame(1, $event->entitlement()->count());
    }

    public function test_missing_sandbox_key_does_not_lock_checkout_claim(): void
    {
        [$owner,$event,$order] = $this->order();
        config(['midtrans.server_key' => null]);
        Http::preventStrayRequests();
        $this->actingAs($owner)->postJson('/organizer/orders/'.$order->id.'/checkout')->assertUnprocessable();
        $this->assertNull($order->fresh()->checkout_claim);
        Http::assertNothingSent();
    }

    public function test_sync_buttons_submit_and_return_link_targets_owned_event(): void
    {
        [$owner, $event, $order] = $this->order();
        Http::preventStrayRequests();
        foreach ([$owner, User::factory()->create(['role' => 'admin'])] as $actor) {
            $prefix = $actor->role === 'admin' ? 'admin' : 'organizer';
            $response = $this->actingAs($actor)->get("/$prefix/orders/$order->id")->assertOk();
            $document = new \DOMDocument;
            @$document->loadHTML($response->getContent());
            $xpath = new \DOMXPath($document);
            $buttons = $xpath->query('//form[@action="'.route($prefix.'.orders.sync', $order).'"]//button');
            $this->assertSame(1, $buttons->length);
            $this->assertSame('submit', $buttons->item(0)->getAttribute('type'));
            if ($prefix === 'organizer') {
                $link = $xpath->query('//a[@href="'.route('organizer.events.show', $event).'"][contains(., "Kembali ke event")]')->item(0);
                $this->assertNotNull($link);
                $this->assertStringContainsString('inline-flex', $link->getAttribute('class'));
                $this->assertStringContainsString('focus:ring', $link->getAttribute('class'));
            }
        }
        Http::assertNothingSent(); // Rendering a GET does not mutate payment or call the gateway.
    }

    public function test_json_sync_uses_gateway_and_repeated_paid_check_is_idempotent(): void
    {
        [$owner, $event, $order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fakeSequence('api.sandbox.midtrans.com/*')
            ->push($this->response($order, 'pending'))
            ->push($this->response($order))
            ->push($this->response($order));
        $url = "/organizer/orders/$order->id/sync";
        $this->actingAs($owner)->postJson($url, ['status' => 'paid', 'transaction_status' => 'settlement'])
            ->assertOk()->assertExactJson(['status' => 'pending', 'paid' => false, 'activated' => false, 'requires_follow_up' => false]);
        $this->assertNull($order->fresh()->paid_at);
        $this->postJson($url)->assertOk()
            ->assertExactJson(['status' => 'paid', 'paid' => true, 'activated' => true, 'requires_follow_up' => false]);
        $paid = $order->fresh();
        $this->postJson($url)->assertOk()->assertJsonPath('status', 'paid')->assertDontSee('test-key');
        $this->assertTrue($paid->paid_at->equalTo($order->fresh()->paid_at));
        $this->assertTrue($paid->activated_at->equalTo($order->fresh()->activated_at));
        $this->assertSame(2, PaymentEvent::where('order_id', $order->id)->count()); // One pending + one settlement receipt.
        $this->assertSame(1, $event->entitlement()->count());
    }

    public function test_sync_without_javascript_redirects_with_server_status(): void
    {
        [$owner, $event, $order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake(['api.sandbox.midtrans.com/*' => Http::response($this->response($order))]);
        $url = route('organizer.orders.show', $order);
        $this->actingAs($owner)->from($url)->post("/organizer/orders/$order->id/sync")
            ->assertRedirect($url)->assertSessionHas('success');
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(1, $event->entitlement()->count());
    }

    public function test_json_sync_forbids_other_owner_and_volunteer_before_gateway_call(): void
    {
        [$owner, $event, $order] = $this->order();
        Http::preventStrayRequests();
        foreach ([
            ['role' => 'organizer', 'organizer_status' => 'active'],
            ['role' => 'volunteer'],
        ] as $attributes) {
            $this->actingAs(User::factory()->create($attributes))
                ->postJson("/organizer/orders/$order->id/sync")->assertForbidden();
        }
        $this->actingAs(User::factory()->create(['role' => 'organizer', 'organizer_status' => 'pending']))
            ->postJson("/organizer/orders/$order->id/sync")->assertRedirect(route('organizer.profile.pending'));
        $owner->forceFill(['is_active' => false])->save();
        $this->actingAs($owner)->postJson("/organizer/orders/$order->id/sync")->assertForbidden();
        Http::assertNothingSent();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_status_check_is_bounded_and_timeout_keeps_payment_pending(): void
    {
        [$owner, $event, $order] = $this->order();
        config(['midtrans.server_key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake(function ($request, $options) {
            $this->assertSame(2, $options['connect_timeout']);
            $this->assertSame(5, $options['timeout']);
            throw new ConnectionException('Simulated timeout');
        });
        $this->actingAs($owner)->postJson("/organizer/orders/$order->id/sync")
            ->assertUnprocessable()->assertJsonValidationErrors('payment');
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->paid_at);
        $this->assertNull($order->fresh()->activated_at);
        $this->assertSame(0, $event->entitlement()->count());
        $this->assertSame(0, PaymentEvent::where('order_id', $order->id)->count());
    }

    public function test_ordinary_order_visit_does_not_enable_automatic_check_but_checkout_return_does(): void
    {
        [$owner, $event, $order] = $this->order();
        $order->forceFill(['checkout_url' => 'https://app.sandbox.midtrans.com/snap/v3/redirection/test-token'])->save();
        Http::preventStrayRequests();
        $this->actingAs($owner)->get("/organizer/orders/$order->id")
            ->assertOk()->assertViewHas('checkPayment', false);
        $this->get("/organizer/orders/$order->id?check_payment=1&transaction_status=settlement")
            ->assertOk()->assertViewHas('checkPayment', true);
        Http::assertNothingSent();
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->paid_at);
        $this->assertSame(0, $event->entitlement()->count());
    }
}
