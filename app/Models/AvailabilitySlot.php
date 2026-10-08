<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilitySlot extends Model
{
    protected $fillable = ['user_id', 'starts_at', 'ends_at'];

    protected $casts = ['starts_at' => 'immutable_datetime', 'ends_at' => 'immutable_datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
