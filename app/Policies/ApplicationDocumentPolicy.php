<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\User;

class ApplicationDocumentPolicy
{
    public function create(User $user, Application $application): bool
    {
        return $user->is_active
            && $user->role === 'volunteer'
            && $application->volunteer_id === $user->id
            && $application->status === 'draft';
    }

    public function delete(User $user, ApplicationDocument $document): bool
    {
        $application = $document->application;

        return $user->is_active
            && $user->role === 'volunteer'
            && $application->volunteer_id === $user->id
            && $application->status === 'draft';
    }

    public function download(User $user, ApplicationDocument $document): bool
    {
        if (! $user->is_active) {
            return false;
        }

        $document->loadMissing(['application.event']);
        $application = $document->application;

        if (! $application) {
            return false;
        }

        if (
            $user->role === 'volunteer'
            && $application->volunteer_id === $user->id
        ) {
            return true;
        }

        if (
            $user->role === 'organizer'
            && $application->event?->organizer_id === $user->id
            && $application->status !== 'draft'
        ) {
            return true;
        }

        return false;
    }
}
