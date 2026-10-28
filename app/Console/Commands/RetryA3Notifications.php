<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Event;
use App\Services\A2Dependencies;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RetryA3Notifications extends Command
{
    protected $signature = 'a3:retry-notifications {--after=0} {--limit=100}';

    protected $description = 'Replay notifikasi A3 dari audit ke NotificationService A4, tanpa outbox tandingan';

    public function handle(A2Dependencies $dependencies): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        $after = filter_var($this->option('after'), FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1 || $limit > 1000 || $after === false || $after < 0) {
            $this->error('Limit 1–1000 dan after minimal 0.');

            return self::FAILURE;
        }
        if (! class_exists(NotificationService::class) && ! app()->bound(NotificationService::class)) {
            $this->warn('Notifikasi tertunda: NotificationService A4 belum tersedia.');

            return self::FAILURE;
        }
        $logs = AuditLog::where('id', '>', $after)->whereIn('action', [
            'application.submitted', 'application.accepted', 'application.rejected', 'application.withdrawn', 'application.cancelled', 'attendance.recorded', 'event.completed',
        ])->orderBy('id')->limit($limit)->get();
        foreach ($logs as $log) {
            try {
                DB::transaction(function () use ($log, $dependencies) {
                    $targets = [];
                    $payload = ['type' => $log->action];
                    if ($log->subject_type === (new Application)->getMorphClass()) {
                        $application = Application::find($log->subject_id);
                        if ($application) {
                            $targets = [$log->action === 'application.submitted' ? $application->event->organizer : $application->volunteer];
                            $payload['application_id'] = $application->id;
                        }
                    } elseif ($log->subject_type === (new Attendance)->getMorphClass()) {
                        $attendance = Attendance::with('application.volunteer')->find($log->subject_id);
                        if ($attendance) {
                            $targets = [$attendance->application->volunteer];
                            $payload['application_id'] = $attendance->application_id;
                        }
                    } elseif ($log->subject_type === (new Event)->getMorphClass()) {
                        $event = Event::find($log->subject_id);
                        if ($event) {
                            $targets = $event->applications()->where('status', 'accepted')->with('volunteer')->get()->pluck('volunteer');
                            $payload['event_id'] = $event->id;
                        }
                    }
                    foreach ($targets as $recipient) {
                        $dependencies->notify($log->action.':'.$log->subject_id.':'.($log->after['revision'] ?? 0).':'.$recipient->id, $recipient, $payload);
                    }
                });
            } catch (\Throwable) {
                $this->warn('Audit #'.$log->id.' tertunda; ulangi dari after sebelumnya.');

                return self::FAILURE;
            }
        }
        $this->info('Replay ke A4 selesai; audit terakhir: '.($logs->last()?->id ?? $after).'. Pengiriman mengikuti worker A4.');

        return self::SUCCESS;
    }
}
