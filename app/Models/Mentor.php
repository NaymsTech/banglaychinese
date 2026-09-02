<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mentor extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'expertise',
        'hourly_rate',
        'bio',
        'is_available',
        'profile_image',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(MentorAvailability::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(MentorshipBooking::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }
}
