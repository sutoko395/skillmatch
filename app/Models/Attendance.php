<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['recorded_at' => 'immutable_datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
