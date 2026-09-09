<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    public const SOURCE_ENROLLMENT = 'enrollment';

    public const SOURCE_DIGITAL_ORDER = 'digital_order';

    public const SOURCE_SERVICE_ORDER = 'service_order';

    public const PAYMENT_STATE_UNPAID = 'unpaid';

    public const PAYMENT_STATE_PARTIALLY_PAID = 'partially_paid';

    public const PAYMENT_STATE_PAID = 'paid';

    protected $fillable = [
        'legacy_source',
        'legacy_id',
        'user_id',
        'student_name',
        'student_email',
        'student_phone',
        'total_amount',
        'currency',
        'order_status',
        'admin_notes',
        'order_received_email_sent_at',
        'outcome_email_sent_at',
        'rejection_email_sent_at',
        'attention_email_sent_at',
        'payment_reminder_sent_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_received_email_sent_at' => 'datetime',
        'outcome_email_sent_at' => 'datetime',
        'rejection_email_sent_at' => 'datetime',
        'attention_email_sent_at' => 'datetime',
        'payment_reminder_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Sum of payment amounts that cleared as paid.
     */
    public function paidTotal(): float
    {
        return (float) $this->payments
            ->where('status', Payment::STATUS_PAID)
            ->sum('amount');
    }

    /**
     * Sum of payment amounts that were refunded back to the buyer.
     */
    public function refundedTotal(): float
    {
        return (float) $this->payments
            ->where('status', Payment::STATUS_REFUNDED)
            ->sum('amount');
    }

    /**
     * Money actually held: paid minus refunded.
     */
    public function netReceived(): float
    {
        return round($this->paidTotal() - $this->refundedTotal(), 2);
    }

    /**
     * Derived review state expressed with the legacy enrollment vocabulary
     * (pending / needs_attention / paid / partially_paid / rejected /
     * refunded) so admin displays keep their historical wording. Computed
     * from the Payment rows; never stored.
     */
    public function reviewStatus(): string
    {
        $payments = $this->payments;
        $total = (float) $this->total_amount;
        $net = $this->netReceived();

        $hasPaid = $payments->contains(fn (Payment $payment): bool => $payment->status === Payment::STATUS_PAID);
        $hasPending = $payments->contains(fn (Payment $payment): bool => $payment->status === Payment::STATUS_PENDING);
        $hasNeeds = $payments->contains(fn (Payment $payment): bool => $payment->status === Payment::STATUS_NEEDS_ATTENTION);
        $hasRejected = $payments->contains(fn (Payment $payment): bool => $payment->status === Payment::STATUS_REJECTED);
        $hasRefunded = $payments->contains(fn (Payment $payment): bool => $payment->status === Payment::STATUS_REFUNDED);

        if (! $hasPaid && ! $hasPending && ! $hasNeeds && ! $hasRejected && $hasRefunded) {
            return Enrollment::PAYMENT_STATUS_REFUNDED;
        }

        if (! $hasPaid && ! $hasPending && ! $hasNeeds && $hasRejected) {
            return Enrollment::PAYMENT_STATUS_REJECTED;
        }

        if ($hasNeeds) {
            return Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION;
        }

        if ($hasPending && ! $hasPaid) {
            return Enrollment::PAYMENT_STATUS_PENDING;
        }

        if ($hasPaid && $net >= $total - 0.009) {
            return Enrollment::PAYMENT_STATUS_PAID;
        }

        if ($hasPaid && $net > 0) {
            return Enrollment::PAYMENT_STATUS_PARTIALLY_PAID;
        }

        if ($hasRefunded) {
            return Enrollment::PAYMENT_STATUS_REFUNDED;
        }

        return Enrollment::PAYMENT_STATUS_PENDING;
    }

    /**
     * Remaining balance: contract price minus net received (paid minus
     * refunded), never below zero.
     */
    public function dueTotal(): float
    {
        $received = $this->paidTotal() - $this->refundedTotal();

        return max(0.0, (float) $this->total_amount - $received);
    }

    /**
     * Derived money state of the order. Nothing is stored; the state is
     * recomputed from the payments each time it is read.
     */
    public function paymentState(): string
    {
        $total = (float) $this->total_amount;

        if ($total <= 0) {
            return self::PAYMENT_STATE_PAID;
        }

        $received = $this->paidTotal() - $this->refundedTotal();

        if ($received >= $total) {
            return self::PAYMENT_STATE_PAID;
        }

        if ($received > 0) {
            return self::PAYMENT_STATE_PARTIALLY_PAID;
        }

        return self::PAYMENT_STATE_UNPAID;
    }
}
