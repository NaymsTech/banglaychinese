<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipApplication extends Model
{
    public const JOURNEY_STATUS_NEW = 'new';

    public const JOURNEY_STATUS_DOCUMENTS_REVIEWED = 'documents_reviewed';

    public const JOURNEY_STATUS_APPLIED = 'applied_to_university';

    public const JOURNEY_STATUS_OFFER = 'offer_received';

    public const JOURNEY_STATUS_VISA = 'visa_processing';

    public const JOURNEY_STATUS_APPROVED = 'approved';

    public const JOURNEY_STATUS_REJECTED = 'rejected';

    public const JOURNEY_STATUSES = [
        self::JOURNEY_STATUS_NEW => 'New',
        self::JOURNEY_STATUS_DOCUMENTS_REVIEWED => 'Documents Reviewed',
        self::JOURNEY_STATUS_APPLIED => 'Applied to University',
        self::JOURNEY_STATUS_OFFER => 'Offer Received',
        self::JOURNEY_STATUS_VISA => 'Visa Processing',
        self::JOURNEY_STATUS_APPROVED => 'Approved',
        self::JOURNEY_STATUS_REJECTED => 'Rejected',
    ];

    public const JOURNEY_STATUS_COLORS = [
        self::JOURNEY_STATUS_NEW => 'gray',
        self::JOURNEY_STATUS_DOCUMENTS_REVIEWED => 'info',
        self::JOURNEY_STATUS_APPLIED => 'purple',
        self::JOURNEY_STATUS_OFFER => 'warning',
        self::JOURNEY_STATUS_VISA => 'sky',
        self::JOURNEY_STATUS_APPROVED => 'success',
        self::JOURNEY_STATUS_REJECTED => 'danger',
    ];

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
        'journey_status',
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
