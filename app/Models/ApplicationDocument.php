<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'uploader_id',
        'document_type',
        'disk',
        'storage_key',
        'original_name',
        'mime_type',
        'size_bytes',
        'checksum_sha256',
        'status',
        'ready_at',
        'purged_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'ready_at' => 'datetime',
            'purged_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }
}
