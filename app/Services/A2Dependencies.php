<?php

namespace App\Services;

use App\Contracts\AssessmentReadiness;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class A2Dependencies
{
    public function assessmentVersion(EventPosition $position): int
    {
        if (! app()->bound(AssessmentReadiness::class)) {
            throw ValidationException::withMessages(['assessment' => 'Validasi assessment belum tersedia. Pengajuan/publikasi menunggu integrasi A4.']);
        }
        $version = app(AssessmentReadiness::class)->publishedVersion($position);
        if ($version < 1) {
            throw ValidationException::withMessages(['assessment' => 'Assessment belum siap.']);
        }

        return $version;
    }

    public function cancelApplications(Event $event, User $actor, string $reason): void
    {
        if (! class_exists(EventCancellationService::class) && ! app()->bound(EventCancellationService::class)) {
            throw ValidationException::withMessages(['cancellation' => 'Pembatalan menunggu layanan lamaran/attempt A3 dan A4.']);
        }
        app(EventCancellationService::class)->cancelApplications($event, $actor, $reason);
    }

    public function notify(string $key, User $recipient, array $payload): bool
    {
        if (! class_exists(NotificationService::class) && ! app()->bound(NotificationService::class)) {
            return false;
        }
        app(NotificationService::class)->enqueue($key, $recipient, $payload);

        return true;
    }
}
