<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_position_id',
        'skill_id',
        'minimum_level', 'is_required',
    ];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(EventPosition::class, 'event_position_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
