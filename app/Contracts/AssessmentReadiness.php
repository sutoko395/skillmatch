<?php

namespace App\Contracts;

use App\Models\EventPosition;

// A4 binds this read-only check. Missing binding blocks submit/publication, never returns fake readiness.
interface AssessmentReadiness
{
    /** Validate questions/options/duration/frozen version; throw ValidationException if incomplete. */
    public function publishedVersion(EventPosition $position): int;
}
