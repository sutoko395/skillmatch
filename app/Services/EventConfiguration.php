<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Validation\ValidationException;

class EventConfiguration
{
    public function assertValid(Event $event, bool $assessment = true): void
    {
        $event->load(['organizer', 'cityRecord', 'category', 'positions.positionSkills.skill', 'positions.schedules', 'positions.requirements']);
        $fail = fn ($message) => throw ValidationException::withMessages(['event' => $message]);
        if (! $event->organizer->is_active || ! $event->organizer->hasVerifiedEmail() || $event->organizer->organizer_status !== 'active') {
            $fail('Organisasi belum terverifikasi atau akun tidak aktif.');
        }
        if (! $event->cityRecord?->is_active || ! $event->category?->is_active) {
            $fail('Pilih kota dan kategori aktif.');
        }
        if (! $event->starts_at || ! $event->ends_at || $event->ends_at <= $event->starts_at || $event->ends_at <= now()) {
            $fail('Rentang event belum valid atau sudah berakhir.');
        }
        if (! $event->registration_opens_at || $event->registration_deadline <= $event->registration_opens_at || $event->registration_deadline > $event->starts_at) {
            $fail('Rentang pendaftaran tidak valid.');
        }
        $package = $event->package_snapshot;
        if (! $package || $event->positions->isEmpty() || $event->positions->count() > $package['max_positions']) {
            $fail('Pilih paket dan lengkapi posisi sesuai batas paket.');
        }
        if ($event->registration_opens_at->diffInSeconds($event->registration_deadline) > $package['max_registration_days'] * 86400) {
            $fail('Durasi pendaftaran melebihi paket.');
        }
        foreach ($event->positions as $position) {
            if ($position->quota < 1 || $position->positionSkills->isEmpty() || $position->schedules->isEmpty()) {
                $fail('Setiap posisi memerlukan kuota, skill dan jadwal.');
            }
            foreach ($position->positionSkills as $skill) {
                if (! $skill->skill?->is_active) {
                    $fail('Skill posisi sudah nonaktif.');
                }
            }
            foreach ($position->schedules as $schedule) {
                if ($schedule->ends_at <= $schedule->starts_at || $schedule->starts_at < $event->starts_at || $schedule->ends_at > $event->ends_at || $event->registration_deadline > $schedule->starts_at) {
                    $fail('Jadwal posisi di luar rentang atau mendahului deadline.');
                }
            }
            if ($assessment) {
                app(A2Dependencies::class)->assessmentVersion($position);
            }
        }
    }
}
