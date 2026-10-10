<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentOption extends Model
{
    public $timestamps = false;

    protected $fillable = ['assessment_question_id', 'label', 'text', 'is_correct'];

    protected $hidden = ['is_correct'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }
}
