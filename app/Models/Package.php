<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = ['name', 'price', 'max_positions', 'max_applications', 'max_registration_days', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'is_active' => 'boolean'];
    }

    public function snapshot(): array
    {
        return $this->only(['id', 'name', 'price', 'max_positions', 'max_applications', 'max_registration_days']);
    }
}
