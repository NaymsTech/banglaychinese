<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorAvailability extends Model
{
    protected $fillable = [
        'mentor_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Mentor::class);
    }
}
