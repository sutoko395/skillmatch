<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function createOrder(Event $event, User $actor): Order
    {
        return DB::transaction(function () use ($event, $actor) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor)->authorize('update', $event);
            if ($event->status !== 'approved' || $event->lifecycle_status !== 'upcoming' || $event->publication_status === 'suspended' || $event->ends_at <= now()) {
                throw ValidationException::withMessages(['payment' => 'Pembayaran hanya untuk event approved yang masih berlaku.']);
            }
            $s = $event->package_snapshot;
            if (! $s || $s['price'] < 1) {
                throw ValidationException::withMessages(['payment' => 'Paket gratis tidak memerlukan order pembayaran.']);
            }
            $existing = $event->orders()->whereIn('status', ['pending', 'paid', 'review_required'])->latest('id')->first();
            if ($existing) {
                return $existing;
            }
            if ($event->entitlement()->exists()) {
                throw ValidationException::withMessages(['payment' => 'Hak paket sudah aktif.']);
            }
            $order = new Order;
            $order->forceFill(['event_id' => $event->id, 'package_id' => $s['id'], 'order_ref' => 'SM-'.Str::uuid(), 'package_snapshot' => $s, 'amount' => $s['price'], 'currency' => 'IDR', 'status' => 'pending'])->save();
            app(AuditService::class)->record($actor, 'order.created', $order, ['after' => ['status' => 'pending']]);

            return $order;
        }, 3);
    }

    public function checkout(Order $order, User $actor): Order
    {
        app(MidtransGateway::class)->assertConfigured();
        $claim = (string) Str::uuid();
        $order = DB::transaction(function () use ($order, $actor, $claim) {
            User::lockForUpdate()->findOrFail($order->event->organizer_id);
            $event = Event::lockForUpdate()->findOrFail($order->event_id);
            $order = Order::lockForUpdate()->findOrFail($order->id);
            Gate::forUser($actor->fresh())->authorize('update', $event);
            if ($event->status !== 'approved' || $event->lifecycle_status !== 'upcoming' || $event->publication_status === 'suspended' || $event->ends_at <= now()) {
                throw ValidationException::withMessages(['payment' => 'Event tidak dapat dibayar.']);
            }
            if ($order->checkout_url || $order->status !== 'pending') {
                return $order;
            }
            if ($order->checkout_claim && $order->checkout_started_at > now()->subMinutes(5)) {
                throw ValidationException::withMessages(['payment' => 'Checkout sedang diproses. Muat ulang halaman status.']);
            }
            $order->forceFill(['checkout_claim' => $claim, 'checkout_started_at' => now()])->save();

            return $order;
        }, 3);
        if ($order->checkout_url || $order->status !== 'pending') {
            return $order;
        }
        // Network call intentionally outside transaction. Same reference protects ambiguous retries.
        $url = app(MidtransGateway::class)->checkout($order);
        DB::transaction(function () use ($order, $claim, $url) {
            $fresh = Order::lockForUpdate()->findOrFail($order->id);
            if ($fresh->checkout_claim === $claim && $fresh->status === 'pending') {
                $fresh->forceFill(['checkout_url' => $url, 'checkout_claim' => null])->save();
            }
        }, 3);

        return $order->fresh();
    }

    public function sync(Order $order): Order
    {
        $verified = app(MidtransGateway::class)->status($order);
        $result = $this->reconcile($order, $verified);
        if ($result->status === 'paid' && ! $result->requires_follow_up && $result->activated_at) {
            try {
                app(EventPublicationService::class)->publish($result->event, $result->event->organizer);
            } catch (ValidationException) { /* Payment stays recorded; event page explains unmet publication prerequisites. */
            }
        }

        return $result;
    }

    public function reconcile(Order $order, VerifiedGatewayResult $result): Order
    {
        return DB::transaction(function () use ($order, $result) {
            $owner = User::lockForUpdate()->findOrFail($order->event->organizer_id);
            $event = Event::lockForUpdate()->findOrFail($order->event_id);
            $order = Order::lockForUpdate()->findOrFail($order->id);
            if ($result->reference !== $order->order_ref || $result->amount !== $order->amount || $result->currency !== $order->currency) {
                throw new \InvalidArgumentException('Verified result does not match order.');
            }
            $receipt = PaymentEvent::where('gateway_event_key', $result->key())->lockForUpdate()->first();
            if (! $receipt) {
                $receipt = new PaymentEvent;
                $receipt->forceFill(['order_id' => $order->id, 'gateway_event_key' => $result->key(), 'gateway_status' => $result->gatewayStatus,
                    'verified_summary' => ['status' => $result->status, 'currency' => $result->currency, 'amount' => $result->amount]])->save();
                $entitlement = $event->entitlement()->lockForUpdate()->first();
                $previous = $order->status;
                $alreadyPaid = (bool) $order->paid_at;
                // Late pending/failed/expired messages never erase historical payment.
                $next = $order->paid_at ? ($result->status === 'review_required' ? 'review_required' : $order->status) : $result->status;
                $order->forceFill(['status' => $next, 'gateway_checked_at' => now(), 'revision' => $order->revision + 1]);
                if ($result->status === 'paid') {
                    $order->paid_at ??= now();
                    if ($order->status !== 'review_required') {
                        $order->status = 'paid';
                    }
                    $order->requires_follow_up = in_array($event->lifecycle_status, ['cancelled', 'completed'])
                        || ($entitlement && $entitlement->order_id !== $order->id)
                        || ($alreadyPaid && $previous === 'review_required');
                }
                if ($result->status === 'review_required') {
                    $order->requires_follow_up = true;
                }
                $order->save();
                if ($order->status === 'paid' && ! $order->requires_follow_up) {
                    app(EntitlementService::class)->activate($event, $order);
                }
                if ($result->status === 'review_required' && $event->publication_status === 'published') {
                    $event->forceFill(['publication_status' => 'suspended', 'revision' => $event->revision + 1])->save();
                    app(AuditService::class)->record(null, 'event.payment_review', $event, ['after' => ['publication_status' => 'suspended']]);
                }
                app(AuditService::class)->record(null, 'payment.reconciled', $order, ['before' => ['status' => $previous], 'after' => ['status' => $order->status, 'revision' => $order->revision]]);
            }
            if (! $receipt->notified_at && app(A2Dependencies::class)->notify('payment.verified:'.$receipt->id.':1:'.$owner->id, $owner, ['type' => 'payment.verified', 'order_id' => $order->id, 'status' => $receipt->verified_summary['status']])) {
                $receipt->forceFill(['notified_at' => now()])->save();
            }

            return $order->fresh();
        }, 3);
    }
}
