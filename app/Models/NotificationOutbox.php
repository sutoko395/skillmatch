<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationOutbox extends Model
{
    protected $table = 'notification_outbox';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['data' => 'array', 'attempts' => 'integer', 'available_at' => 'immutable_datetime',
            'claimed_at' => 'immutable_datetime', 'delivered_at' => 'immutable_datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
