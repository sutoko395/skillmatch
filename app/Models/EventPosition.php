<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'quota',
        'min_skill_level',
        'qualifications',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function positionSkills(): HasMany
    {
        return $this->hasMany(PositionSkill::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(PositionRequirement::class);
    }
}