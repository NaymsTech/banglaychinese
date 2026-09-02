<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorshipBooking extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_OPTIONS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_CONFIRMED => 'Confirmed',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $fillable = [
        'student_id',
        'mentor_id',
        'scheduled_at',
        'status',
        'meeting_link',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Mentor::class);
    }
}
