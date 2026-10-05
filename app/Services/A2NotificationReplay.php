<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class A2NotificationReplay
{
    // Reuse the business audit/payment ledger; A4 still exclusively owns the notification outbox.
    public function run(): array
    {
        $sent = 0;
        $pending = 0;
        foreach (AuditLog::where('subject_type', (new Event)->getMorphClass())->whereIn('action', ['event.published', 'event.cancelled'])->lazyById(100) as $audit) {
            $event = Event::with('organizer')->find($audit->subject_id);
            if (! $event) {
                continue;
            }
            $revision = $audit->after['revision'] ?? $audit->id;
            $ok = DB::transaction(fn () => app(A2Dependencies::class)->notify($audit->action.':'.$event->id.':'.$revision.':'.$event->organizer_id, $event->organizer, ['event_id' => $event->id, 'type' => $audit->action]), 3);
            $ok ? $sent++ : $pending++;
        }
        foreach (PaymentEvent::whereNull('notified_at')->lazyById(100) as $receipt) {
            $ok = DB::transaction(function () use ($receipt) {
                $order = $receipt->order;
                $owner = User::lockForUpdate()->findOrFail($order->event->organizer_id);
                Event::lockForUpdate()->findOrFail($order->event_id);
                Order::lockForUpdate()->findOrFail($order->id);
                $receipt = PaymentEvent::lockForUpdate()->findOrFail($receipt->id);
                if ($receipt->notified_at) {
                    return true;
                }
                if (! app(A2Dependencies::class)->notify('payment.verified:'.$receipt->id.':1:'.$owner->id, $owner, ['type' => 'payment.verified', 'order_id' => $order->id, 'status' => $receipt->verified_summary['status']])) {
                    return false;
                }
                $receipt->forceFill(['notified_at' => now()])->save();

                return true;
            }, 3);
            $ok ? $sent++ : $pending++;
        }

        return compact('sent', 'pending');
    }
}
