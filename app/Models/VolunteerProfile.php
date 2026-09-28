<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VolunteerProfile extends Model
{
    protected $fillable = [
        'user_id', 'phone', 'bio', 'location', 
        'availability', 'cv_file', 'portfolio_file'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}