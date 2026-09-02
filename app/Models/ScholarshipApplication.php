<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipApplication extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'highest_qualification',
        'gpa_cgpa',
        'desired_program',
        'interested_service_id',
        'target_intake',
        'hsk_english_level',
        'target_course',
        'educational_background',
        'statement_of_purpose',
        'message',
        'status',
        'application_status',
        'admin_notes',
        'follow_up_date',
        'budget',
        'preferred_consultation_time',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    /**
     * The Study in China service this lead is interested in.
     */
    public function interestedService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'interested_service_id');
    }

    /**
     * The scholarship review outcome is recorded as null until a decision is
     * made. The legacy UI refers to that state as "pending".
     */
    public function setApplicationStatusAttribute(?string $value): void
    {
        $this->attributes['application_status'] = ($value === 'pending' || $value === '') ? null : $value;
    }
}
