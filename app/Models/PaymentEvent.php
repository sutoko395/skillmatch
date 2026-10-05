<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentEvent extends Model
{
    protected $fillable = [];

    protected function casts(): array
    {
        return ['verified_summary' => 'array', 'notified_at' => 'immutable_datetime'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
