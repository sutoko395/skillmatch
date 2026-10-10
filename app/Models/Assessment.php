<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = ['event_position_id', 'version', 'revision', 'duration_minutes', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'version' => 'integer', 'revision' => 'integer', 'duration_minutes' => 'integer'];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(EventPosition::class, 'event_position_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('sort_order');
    }
}
