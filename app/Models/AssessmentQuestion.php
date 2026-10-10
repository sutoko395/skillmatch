<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentQuestion extends Model
{
    public $timestamps = false;

    protected $fillable = ['assessment_id', 'sort_order', 'text'];

    public function options(): HasMany
    {
        return $this->hasMany(AssessmentOption::class)->orderBy('label');
    }
}
