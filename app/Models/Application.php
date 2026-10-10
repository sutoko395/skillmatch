<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_position_id',
        'volunteer_id',
        'status',
        'snapshot_json',
        'submitted_at',
        'decision_at',
        'decision_by',
        'decision_reason',
        'revision',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_json' => 'array',
            'submitted_at' => 'datetime',
            'decision_at' => 'datetime',
            'revision' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(EventPosition::class, 'event_position_id');
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function decisionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decision_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function cvDocument(): HasOne
    {
        return $this->hasOne(ApplicationDocument::class)
            ->where('document_type', 'cv')
            ->where('status', 'ready');
    }

    public function supportingDocuments(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class)
            ->where('document_type', 'supporting')
            ->where('status', 'ready');
    }
}
