<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventEntitlement extends Model
{
    protected $fillable = [];

    protected function casts(): array
    {
        return ['package_snapshot' => 'array', 'activated_at' => 'immutable_datetime'];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
