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

        return $application !== null && Gate::forUser($user)->allows('view', $application);
    }
}
