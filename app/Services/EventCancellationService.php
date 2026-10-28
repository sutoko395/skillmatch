<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EventCancellationService
{
    public function cancelApplications(Event $event, User $actor, string $reason): void
    {
        if (DB::transactionLevel() < 1) {
            throw new \LogicException('Cancellation must share the A2 event transaction.');
        }
        $event = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();
        Gate::forUser($actor->fresh())->authorize('update', $event);
        $reason = app(SelectionService::class)->reason($reason);
        foreach ($event->applications()->whereIn('status', ['draft', ...SelectionService::PENDING, 'accepted'])
            ->orderBy('id')->lockForUpdate()->get() as $application) {
            app(SelectionService::class)->transition($application, $actor, 'cancelled', $reason);
            app(AssessmentService::class)->cancelLocked($application, $actor);
        }
    }
}
