<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['recorded_at' => 'immutable_datetime', 'uses_sessions' => 'boolean', 'legacy_record' => 'array'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class)->orderBy('starts_at')->orderBy('id');
    }

    public function participationLabel(): string
    {
        if (! $this->uses_sessions) {
            return match ($this->status) {
                'present' => 'Partisipasi berhasil', 'absent' => 'Tidak hadir', default => 'Belum dicatat'
            };
        }
        if ($this->sessions->isEmpty() || $this->sessions->contains('status', 'unrecorded')) {
            return 'Belum dicatat';
        }
        if (! $this->sessions->contains('status', 'absent')) {
            return 'Partisipasi berhasil';
        }

        return $this->sessions->contains('status', 'present') ? 'Partisipasi tidak lengkap' : 'Tidak hadir';
    }
}
