<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'category_id',
        'title',
        'description',
        'location',
        'city',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'registration_deadline',
        'status',
        'verification_note',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_deadline' => 'immutable_datetime',
            'starts_at' => 'immutable_datetime', 'ends_at' => 'immutable_datetime',
            'registration_opens_at' => 'immutable_datetime', 'published_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime', 'cancelled_at' => 'immutable_datetime', 'submitted_at' => 'immutable_datetime',
            'package_snapshot' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function cityRecord()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function entitlement()
    {
        return $this->hasOne(EventEntitlement::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where('status', 'approved')->where('publication_status', 'published')
            ->whereIn('lifecycle_status', ['upcoming', 'ongoing'])->where('ends_at', '>', now())
            ->whereHas('entitlement')->whereHas('organizer', fn ($q) => $q->where('is_active', true)
            ->where('organizer_status', 'active')->whereNotNull('email_verified_at'));
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function positions()
    {
        return $this->hasMany(EventPosition::class);
    }
}
