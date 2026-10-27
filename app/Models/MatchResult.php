<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchResult extends Model
{
    protected $fillable = ['application_id', 'skill_score', 'availability_score', 'location_score', 'total_score', 'rule_version', 'breakdown_json'];

    protected function casts(): array
    {
        return [
            'skill_score' => 'float', 'availability_score' => 'float', 'location_score' => 'float',
            'total_score' => 'float', 'breakdown_json' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
