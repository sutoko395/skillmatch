<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSession extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['session_date' => 'immutable_date', 'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime', 'recorded_at' => 'immutable_datetime', 'revision' => 'integer'];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
