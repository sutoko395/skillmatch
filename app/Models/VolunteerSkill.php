<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VolunteerSkill extends Model
{
    protected $fillable = ['user_id', 'skill_id', 'level'];

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}