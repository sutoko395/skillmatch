<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\UniqueConstraintViolationException;

class NotificationOutbox extends Model
{
    protected $table = 'notification_outbox';

    protected $guarded = ['id'];

    public static function deduplicate(string $key, array $values): self
    {
        try {
            return static::firstOrCreate(['dedupe_key' => $key], $values);
        } catch (UniqueConstraintViolationException $exception) {
            // InnoDB REPEATABLE READ can retain a snapshot from before the other
            // worker committed. A locking read sees the winning committed row.
            $existing = static::where('dedupe_key', $key)->lockForUpdate()->first();
            if (! $existing) {
                throw $exception;
            }

            return $existing;
        }
    }

    protected function casts(): array
    {
        return ['data' => 'array', 'attempts' => 'integer', 'available_at' => 'immutable_datetime',
            'claimed_at' => 'immutable_datetime', 'delivered_at' => 'immutable_datetime', 'read_at' => 'immutable_datetime'];
    }

    public function label(): string
    {
        if ($this->type === 'event.coordination_updated') {
            return 'Koordinasi peserta diperbarui. Buka detail lamaran untuk melihat informasi terbaru.';
        }

        return match ($this->type) {
            'organizer.reviewed' => 'Hasil peninjauan organisasi: '.match ($this->data['status'] ?? '') {
                'active' => 'disetujui', 'inactive' => 'perlu revisi', default => 'menunggu pemeriksaan'
            },
            'organizer.submitted' => 'Pengajuan organisasi perlu ditinjau',
            'event.approved' => 'Event disetujui Admin', 'event.rejected' => 'Event memerlukan revisi',
            'event.published' => 'Event telah dipublikasikan', 'event.cancelled' => 'Event dibatalkan',
            'payment.verified' => 'Status pembayaran telah diperiksa',
            'application.submitted' => 'Lamaran baru diterima', 'application.accepted' => 'Lamaran diterima',
            'application.rejected' => 'Lamaran ditolak', 'application.withdrawn' => 'Lamaran ditarik',
            'application.cancelled' => 'Lamaran dibatalkan', 'application.screened' => 'Hasil screening tersedia',
            'application.matched' => 'Assessment tersedia', 'application.assessed' => 'Assessment selesai, menunggu peninjauan',
            'attendance.recorded' => 'Kehadiran telah dicatat', 'attendance.session_recorded' => 'Kehadiran sesi tugas telah dicatat', 'event.completed' => 'Kegiatan selesai',
            'recruitment.repaired' => 'Tenggat assessment event lama telah dilengkapi',
            default => 'Pembaruan aktivitas',
        };
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
