<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ApplicationDocumentPolicy
{
    public function create(User $user, Application $application): bool
    {
        return Gate::forUser($user)->allows('update', $application);
    }

    public function delete(User $user, ApplicationDocument $document): bool
    {
        $application = $document->application;

        return $application !== null && $this->create($user, $application);
    }

    public function download(User $user, ApplicationDocument $document): bool
    {
        $application = $document->application;

        if ($application === null || ! Gate::forUser($user)->allows('view', $application)) {
            return false;
        }
        if ($user->role === 'volunteer') {
            return true;
        }
        if (in_array($application->status, ['withdrawn', 'cancelled'], true)
            || $application->event?->lifecycle_status === 'cancelled'
            || ! $application->volunteer?->is_active) {
            return false;
        }
        $event = $application->event;
        $terminalAt = $event->completed_at ?? $event->cancelled_at;

        return ! $terminalAt || now()->lt($terminalAt->addDays(config('a3.document_days')));
    }
}
