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
        'starts_at',
        'ends_at',
        'registration_opens_at',
        'registration_deadline',
        'status',
        'publication_status',
        'lifecycle_status',
        'verification_note',
        'verified_at',
        'revision',
        'submitted_at',
        'published_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'package_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_deadline' => 'immutable_datetime',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'registration_opens_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'submitted_at' => 'immutable_datetime',
            'verified_at' => 'datetime',
            'package_snapshot' => 'array',
        ];
    }

    public function entitlement()
    {
        return $this->hasOne(EventEntitlement::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
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

    public function scopePubliclyVisible($query)
    {
        return $query
            ->where('status', 'approved')
            ->where('publication_status', 'published')
            ->whereIn('lifecycle_status', ['upcoming', 'ongoing'])
            ->where('ends_at', '>', now())
            ->whereHas('entitlement', function ($q) {
                $q->where(function ($q) {
                    $q->whereNull('order_id')
                        ->orWhereHas('order', function ($q) {
                            $q->where('status', 'paid')
                                ->where('requires_follow_up', false);
                        });
                });
            })
            ->whereHas('organizer', function ($q) {
                $q->where('is_active', true)
                    ->where('organizer_status', 'active');
            });
    }
}