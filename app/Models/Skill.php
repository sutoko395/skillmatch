<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'is_active',
    ];

    public function volunteerSkills(): HasMany
    {
        return $this->hasMany(VolunteerSkill::class);
    }

    public function positionSkills(): HasMany
    {
        return $this->hasMany(PositionSkill::class);
    }
}
