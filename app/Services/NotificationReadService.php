<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Assessment;
use App\Models\Event;
use App\Models\NotificationOutbox;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationReadService
{
    public function recent(User $user): array
    {
        $rows = NotificationOutbox::where('recipient_id', $user->id)->where('channel', 'database')
            ->latest('id')->limit(5)->get();

        return $this->items($rows, $user);
    }

    public function items(Collection $notifications, User $user): array
    {
        $rows = $notifications->filter(fn (NotificationOutbox $row) => $row->recipient_id === $user->id && $row->channel === 'database');

        if ($user->role === 'volunteer') {
            return $this->forVolunteer($rows, $user);
        }

        if ($user->role === 'organizer') {
            return $this->forOrganizer($rows, $user);
        }

        return $rows->map(fn (NotificationOutbox $row) => [
            'row' => $row,
            'title' => $row->label(),
            'event' => null,
            'position' => null,
            'message' => $row->type === 'organizer.submitted' ? 'Periksa kelengkapan pengajuan pada antrean verifikasi organisasi.' : null,
            'destination' => route('admin.organizers.index', $row->type === 'organizer.submitted' ? ['status' => 'pending'] : []),
            'action' => $row->type === 'organizer.submitted' ? 'Buka antrean verifikasi' : 'Buka daftar Organizer',
            'icon' => $row->type === 'organizer.submitted' ? 'organization' : 'bell',
        ])->all();
    }

    private function forVolunteer(Collection $rows, User $user): array
    {
        $applications = Application::where('volunteer_id', $user->id)
            ->whereIn('id', $rows->pluck('application_id')->filter())
            ->with(['event', 'position', 'assessmentAttempt', 'screeningResult', 'matchResult'])->get()->keyBy('id');

        $events = Event::whereIn('id', $rows->pluck('data.event_id')->filter())
            ->whereHas('applications', fn ($q) => $q->where('volunteer_id', $user->id)->whereNotNull('submitted_at'))
            ->get()->keyBy('id');

        return $rows->map(function (NotificationOutbox $row) use ($applications, $events) {
            $application = $applications->get($row->application_id);
            $event = $application?->event ?? $events->get($row->data['event_id'] ?? null);
            $position = $application?->snapshot_json['position_name'] ?? $application?->position?->name;
            $destination = $application ? route('volunteer.applications.show', $application) : route('volunteer.applications.index');
            $action = $application ? 'Detail lamaran' : 'Buka Lamaran Saya';
            $title = $row->label();
            $icon = match ($row->type) {
                'application.accepted' => 'check',
                'application.rejected', 'application.cancelled', 'application.withdrawn', 'event.cancelled' => 'closed',
                'application.screened', 'application.assessed' => 'assessment',
                'attendance.recorded', 'attendance.session_recorded', 'event.completed' => 'calendar',
                default => 'bell',
            };
            $message = match ($row->type) {
                'application.accepted' => 'Anda terpilih. Cek jadwal dan informasi koordinasi di detail lamaran.',
                'application.rejected' => 'Lamaran ini tidak terpilih. Buka detail untuk melihat hasil seleksi.',
                'application.screened' => 'Buka detail lamaran untuk melihat hasil penyaringan dan tahap berikutnya.',
                'application.assessed' => 'Asesmen selesai. Pantau keputusan penyelenggara di detail lamaran.',
                'event.coordination_updated' => 'Cek informasi terbaru dari penyelenggara di detail lamaran.',
                'attendance.recorded', 'attendance.session_recorded' => 'Buka detail lamaran untuk memeriksa pencatatan kehadiran Anda.',
                default => null,
            };

            if ($row->type === 'event.coordination_updated') {
                $title = 'Koordinasi peserta diperbarui';
            }
            if ($row->type === 'event.completed') {
                $message = 'Lihat hasil kehadiran Anda di Riwayat Partisipasi.';
                $destination = route('volunteer.history.index');
                $action = 'Buka riwayat';
            }
            if ($row->type === 'application.matched') {
                $icon = 'assessment';
                $title = 'Pencocokan selesai';
                $message = 'Lihat status asesmen terbaru di detail lamaran.';
                if ($application && $event && $application->status === 'assessment_pending') {
                    $deadline = app(RecruitmentScheduleService::class)->deadlineFor($application);
                    $attempt = $application->assessmentAttempt;
                    $version = $application->snapshot_json['assessment_version'] ?? null;
                    if ($attempt?->status === 'in_progress' && $attempt->expires_at?->isFuture()) {
                        $deadline = $attempt->expires_at;
                    }
                    if ($deadline?->isFuture() && $event->starts_at?->isFuture() && $event->lifecycle_status === 'upcoming'
                        && ($attempt ? $attempt->status === 'in_progress' && $attempt->expires_at?->isFuture()
                            : $event->publication_status === 'published' && $event->published_at
                                && $application->screeningResult?->passed && $application->matchResult && $version
                                && Assessment::where('event_position_id', $application->event_position_id)
                                    ->where('version', $version)->where('is_published', true)->exists())) {
                        $title = $attempt ? 'Asesmen sedang dikerjakan' : 'Asesmen siap dikerjakan';
                        $limit = $deadline->min($event->starts_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i');
                        $message = "Selesaikan sebelum {$limit} WIB.";
                        $destination = route('volunteer.assessments.show', $application);
                        $action = $attempt ? 'Lanjutkan asesmen' : 'Buka asesmen';
                    }
                }
            }

            return [
                'row' => $row, 'title' => $title, 'event' => $event?->title, 'position' => $position,
                'message' => $message, 'destination' => $destination, 'action' => $action, 'icon' => $icon,
            ];
        })->all();
    }

    private function forOrganizer(Collection $rows, User $user): array
    {
        $applications = $events = $orders = collect();
        if ($user->organizer_status === 'active') {
            $ownedEvent = fn ($query) => $query->where('organizer_id', $user->id);
            $applications = Application::whereIn('id', $rows->pluck('application_id')->filter())
                ->whereNotNull('submitted_at')->whereHas('event', $ownedEvent)
                ->with(['event:id,title', 'position:id,name'])->get()->keyBy('id');
            $events = Event::whereIn('id', $rows->pluck('data.event_id')->filter())
                ->where('organizer_id', $user->id)->get(['id', 'title'])->keyBy('id');
            $orders = Order::whereIn('id', $rows->pluck('data.order_id')->filter())
                ->whereHas('event', $ownedEvent)->with('event:id,title')->get(['id', 'event_id'])->keyBy('id');
        }

        return $rows->map(function (NotificationOutbox $row) use ($applications, $events, $orders, $user) {
            $application = $applications->get($row->application_id);
            $order = $orders->get($row->data['order_id'] ?? null);
            $event = $application?->event ?? $order?->event ?? $events->get($row->data['event_id'] ?? null);
            [$destination, $action] = match (true) {
                $application !== null => [route('organizer.applications.show', $application), 'Tinjau lamaran'],
                $order !== null => [route('organizer.orders.show', $order), 'Detail transaksi'],
                $event !== null => [route('organizer.events.show', $event), 'Detail kegiatan'],
                default => [route('organizer.profile.edit'), 'Buka Profil Organisasi'],
            };

            $message = match ($row->type) {
                'organizer.reviewed' => 'Buka profil organisasi untuk melihat hasil dan catatan pemeriksaan.',
                'application.submitted' => $application ? 'Lamaran baru masuk. Buka detail untuk melihat tahap seleksinya.' : null,
                'application.assessed' => $application ? 'Asesmen pelamar selesai. Buka detail untuk meninjau hasilnya.' : null,
                'event.approved' => $event ? 'Periksa syarat publikasi dan status paket di detail kegiatan.' : null,
                'event.rejected' => $event ? 'Baca catatan Admin dan perbaiki kegiatan sebelum mengajukan kembali.' : null,
                'payment.verified' => $order ? 'Buka transaksi untuk melihat status pembayaran terbaru.' : null,
                default => null,
            };
            $icon = match ($row->type) {
                'organizer.reviewed' => 'organization',
                'application.submitted', 'application.assessed' => 'assessment',
                'event.approved', 'event.published' => 'check',
                'event.rejected', 'event.cancelled', 'application.withdrawn' => 'closed',
                'payment.verified' => 'payment',
                default => 'bell',
            };

            return [
                'row' => $row, 'title' => $row->label(), 'event' => $event?->title,
                'position' => $application?->snapshot_json['position_name'] ?? $application?->position?->name,
                'message' => $message, 'destination' => $destination, 'action' => $action, 'icon' => $icon,
            ];
        })->all();
    }
}
