<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningResult extends Model
{
    protected $fillable = ['application_id', 'passed', 'checks_json', 'rule_version', 'evaluated_at'];

    protected function casts(): array
    {
        return ['passed' => 'boolean', 'checks_json' => 'array', 'evaluated_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function evaluation(): array
    {
        return ['passed' => $this->passed, 'checks' => $this->checks_json, 'rule_version' => $this->rule_version];
    }
}
