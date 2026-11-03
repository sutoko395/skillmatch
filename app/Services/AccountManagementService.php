<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\NotificationOutbox;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AccountManagementService
{
    public function delete(User $subject, User $actor): void
    {
        DB::transaction(function () use ($subject, $actor) {
            $subject = User::whereKey($subject->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('delete', $subject);
            Gate::forUser($actor->fresh())->authorize($subject->role === 'organizer' ? 'manageOrganizer' : 'manageVolunteer', $subject);
            $used = $subject->events()->exists() || $subject->volunteerSkills()->exists()
                || $subject->availabilitySlots()->exists()
                || Event::where('recruitment_repaired_by', $subject->id)->exists()
                || ApplicationDocument::where('uploader_id', $subject->id)->exists()
                || Application::where('volunteer_id', $subject->id)->orWhere('decision_by', $subject->id)->exists()
                || AuditLog::where('actor_id', $subject->id)->exists()
                || Attendance::where('recorded_by', $subject->id)->exists()
                || AttendanceSession::where('recorded_by', $subject->id)->exists()
                || NotificationOutbox::where('recipient_id', $subject->id)->exists();
            abort_if($used, 403, 'Akun memiliki data terkait. Nonaktifkan akun untuk mempertahankan dokumen dan riwayat.');
            $paths = $subject->organizerDocuments()->pluck('file_path')->filter()->all();
            app(AuditService::class)->record($actor, 'account.deleted', $subject, [], 'unused_account');
            $subject->delete();
            // Files are untouched until every SQL operation has committed successfully.
            DB::afterCommit(function () use ($paths) {
                foreach ($paths as $path) {
                    Storage::disk('local')->delete($path);
                }
            });
        }, 3);
    }
}
