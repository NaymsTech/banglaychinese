<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'bank' => 'Bank Transfer',
        'other' => 'Other',
    ];

    public const PAYMENT_STATUS_PENDING = 'pending';

    public const PAYMENT_STATUS_PAID = 'paid';

    public const PAYMENT_STATUS_PARTIALLY_PAID = 'partially_paid';

    public const PAYMENT_STATUS_REJECTED = 'rejected';

    public const PAYMENT_STATUS_NEEDS_ATTENTION = 'needs_attention';

    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_PENDING => 'Pending',
        self::PAYMENT_STATUS_PAID => 'Paid',
        self::PAYMENT_STATUS_PARTIALLY_PAID => 'Partially Paid',
        self::PAYMENT_STATUS_REJECTED => 'Rejected',
        self::PAYMENT_STATUS_NEEDS_ATTENTION => 'Needs Attention',
        self::PAYMENT_STATUS_REFUNDED => 'Refunded',
    ];

    public const ENROLLMENT_STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'user_id',
        'course_id',
        'student_name',
        'student_email',
        'student_phone',
        'amount',
        'amount_paid',
        'amount_due',
        'payment_status',
        'enrollment_status',
        'admin_notes',
        // Legacy columns still written by the public checkout flow.
        'status',
        'payment_method',
        'transaction_id',
        'sender_number',
        'rejection_reason',
        'attention_reason',
        'price_paid',
        'paid_at',
        'confirmation_email_sent_at',
        'order_received_email_sent_at',
        'rejection_email_sent_at',
        'attention_email_sent_at',
        'payment_reminder_sent_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'price_paid' => 'decimal:2',
        'paid_at' => 'datetime',
        'confirmation_email_sent_at' => 'datetime',
        'order_received_email_sent_at' => 'datetime',
        'rejection_email_sent_at' => 'datetime',
        'attention_email_sent_at' => 'datetime',
        'payment_reminder_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Keep the remaining balance consistent even when the form's
        // auto-calculation hasn't run (bulk updates, API writes, etc.).
        static::saving(function (Enrollment $enrollment): void {
            if ($enrollment->amount !== null && $enrollment->amount_paid !== null) {
                $enrollment->amount_due = max(0, (float) $enrollment->amount - (float) $enrollment->amount_paid);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function unifiedOrder(): HasOne
    {
        return $this->hasOne(Order::class, 'legacy_id')->where('legacy_source', Order::SOURCE_ENROLLMENT);
    }
}
