<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EventPublicationService
{
    public function publish(Event $event, User $actor): Event
    {
        return DB::transaction(function () use ($event, $actor) {
            $owner = User::lockForUpdate()->findOrFail($event->organizer_id);
            $actor = $actor->id === $owner->id ? $owner : $actor->fresh();
            $event = Event::lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor)->authorize('view', $event);
            if ($event->status !== 'approved' || $event->lifecycle_status !== 'upcoming' || $event->publication_status === 'suspended') {
                throw ValidationException::withMessages(['event' => 'Event belum disetujui, ditangguhkan, atau tidak dapat dipublikasikan.']);
            }
            app(EventConfiguration::class)->assertValid($event);
            $entitlement = $event->entitlement()->first() ?? app(EntitlementService::class)->activate($event);
            if (! $entitlement || $entitlement->package_snapshot !== $event->package_snapshot || ($entitlement->order_id && ($entitlement->order->status !== 'paid' || $entitlement->order->requires_follow_up))) {
                throw ValidationException::withMessages(['event' => 'Hak paket yang sesuai belum aktif.']);
            }
            if ($event->publication_status === 'published') {
                return $event;
            }
            $event->forceFill(['publication_status' => 'published', 'published_at' => $event->published_at ?? now(), 'revision' => $event->revision + 1])->save();
            app(AuditService::class)->record($actor, 'event.published', $event, ['after' => ['publication_status' => 'published', 'revision' => $event->revision]]);
            app(A2Dependencies::class)->notify('event.published:'.$event->id.':'.$event->revision.':'.$owner->id, $owner, ['event_id' => $event->id, 'type' => 'event.published']);

            return $event;
        }, 3);
    }

    public function suspend(Event $event, User $actor): void
    {
        DB::transaction(function () use ($event, $actor) {
            User::lockForUpdate()->findOrFail($event->organizer_id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor->fresh())->authorize('manage', $event);
            if ($event->publication_status !== 'published') {
                throw ValidationException::withMessages(['event' => 'Hanya event published yang dapat ditangguhkan.']);
            }
            $event->forceFill(['publication_status' => 'suspended', 'revision' => $event->revision + 1])->save();
            app(AuditService::class)->record($actor, 'event.suspended', $event, ['after' => ['publication_status' => 'suspended']]);
        }, 3);
    }

    public function resume(Event $event, User $actor): void
    {
        DB::transaction(function () use ($event, $actor) {
            User::lockForUpdate()->findOrFail($event->organizer_id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor->fresh())->authorize('manage', $event);
            if ($event->publication_status !== 'suspended') {
                throw ValidationException::withMessages(['event' => 'Event tidak sedang ditangguhkan.']);
            }
            $event->forceFill(['publication_status' => 'unpublished'])->save();
            $this->publish($event, $actor);
        }, 3);
    }

    public function cancel(Event $event, User $actor, string $reason): void
    {
        if (mb_strlen(trim($reason)) < 5 || mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages(['reason' => 'Alasan pembatalan wajib 5 sampai 1000 karakter.']);
        }
        DB::transaction(function () use ($event, $actor, $reason) {
            User::lockForUpdate()->findOrFail($event->organizer_id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor->fresh())->authorize('update', $event);
            if ($event->lifecycle_status === 'cancelled') {
                return;
            }
            if ($event->lifecycle_status === 'completed') {
                throw ValidationException::withMessages(['event' => 'Event selesai tidak dapat dibatalkan.']);
            }
            app(A2Dependencies::class)->cancelApplications($event, $actor, $reason);
            $event->forceFill(['lifecycle_status' => 'cancelled', 'publication_status' => 'unpublished', 'cancelled_at' => now(), 'cancellation_reason' => $reason, 'revision' => $event->revision + 1])->save();
            app(AuditService::class)->record($actor, 'event.cancelled', $event, ['after' => ['lifecycle_status' => 'cancelled', 'publication_status' => 'unpublished', 'revision' => $event->revision]], 'organizer_request');
            app(A2Dependencies::class)->notify('event.cancelled:'.$event->id.':'.$event->revision.':'.$actor->id, $actor, ['event_id' => $event->id, 'type' => 'event.cancelled']);
        }, 3);
    }
}
