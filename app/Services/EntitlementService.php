<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventEntitlement;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EntitlementService
{
    // Caller holds event lock. Same lock is mandatory for A3 submission accounting.
    public function activate(Event $event, ?Order $order = null): ?EventEntitlement
    {
        if (DB::transactionLevel() < 1) {
            throw new \LogicException('Activation requires an event transaction.');
        }
        if (in_array($event->lifecycle_status, ['completed', 'cancelled'])) {
            return null;
        }
        $snapshot = $order?->package_snapshot ?? $event->package_snapshot;
        if (! $snapshot || $event->status !== 'approved') {
            return null;
        }
        if ($order ? ($order->event_id !== $event->id || $order->status !== 'paid' || $order->amount < 1) : ((int) $snapshot['price'] !== 0)) {
            return null;
        }
        $existing = EventEntitlement::where('event_id', $event->id)->lockForUpdate()->first();
        if ($existing) {
            return $existing;
        }
        $entitlement = new EventEntitlement;
        $entitlement->forceFill(['event_id' => $event->id, 'order_id' => $order?->id, 'package_snapshot' => $snapshot,
            'max_positions' => $snapshot['max_positions'], 'max_applications' => $snapshot['max_applications'],
            'max_registration_days' => $snapshot['max_registration_days'], 'activated_at' => now()])->save();
        if ($order && ! $order->activated_at) {
            $order->forceFill(['activated_at' => $entitlement->activated_at])->save();
        }
        app(AuditService::class)->record(null, 'entitlement.activated', $entitlement);

        return $entitlement;
    }

    // A3 invokes inside its submission transaction after locking volunteer then event.
    // A3 must first check its unique application/submitted_at; rollback also rolls back this counter.
    public function consumeApplication(Event $event): void
    {
        if (DB::transactionLevel() < 1) {
            throw new \LogicException('Submission must be transactional.');
        }
        $event = Event::lockForUpdate()->findOrFail($event->id);
        if (! Event::publiclyVisible()->whereKey($event->id)->exists() || now() < $event->registration_opens_at || now() >= $event->registration_deadline) {
            throw ValidationException::withMessages(['event' => 'Pendaftaran tidak tersedia.']);
        }
        $count = EventEntitlement::where('event_id', $event->id)->whereColumn('submitted_applications', '<', 'max_applications')->increment('submitted_applications');
        if ($count !== 1) {
            throw ValidationException::withMessages(['event' => 'Batas lamaran paket tercapai.']);
        }
    }
}
