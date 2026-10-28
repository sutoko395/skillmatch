<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentPurge extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['purged_at' => 'immutable_datetime'];
    }
}
