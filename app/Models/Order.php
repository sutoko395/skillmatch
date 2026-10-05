<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [];

    protected function casts(): array
    {
        return ['package_snapshot' => 'array', 'amount' => 'integer', 'paid_at' => 'immutable_datetime', 'activated_at' => 'immutable_datetime', 'checkout_started_at' => 'immutable_datetime', 'gateway_checked_at' => 'immutable_datetime', 'requires_follow_up' => 'boolean', 'checkout_url' => 'encrypted'];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
