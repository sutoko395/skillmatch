<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCoordination extends Model
{
    protected $guarded = ['id'];

    // Private coordination is exposed explicitly only after authorization, never via event JSON.
    protected $hidden = ['group_url', 'pic_name', 'pic_contact'];

    protected function casts(): array
    {
        return ['revision' => 'integer'];
    }
}
