<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionSchedule extends Model
{
    protected $fillable = ['event_position_id', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return ['starts_at' => 'immutable_datetime', 'ends_at' => 'immutable_datetime'];
    }

    public function position()
    {
        return $this->belongsTo(EventPosition::class, 'event_position_id');
    }
}
