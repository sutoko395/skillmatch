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
        'qualifications', 'required_full_availability', 'required_same_city', 'follows_event_schedule',
    ];

    protected function casts(): array
    {
        return ['required_full_availability' => 'boolean', 'required_same_city' => 'boolean', 'follows_event_schedule' => 'boolean'];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PositionSchedule::class);
    }

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

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'event_position_id');
    }
}
