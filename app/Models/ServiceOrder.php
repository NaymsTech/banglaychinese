<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrder extends Model
{
    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'bank' => 'Bank Transfer',
        'other' => 'Other',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'partially_paid' => 'Partially Paid',
        'refunded' => 'Refunded',
    ];

    public const ENROLLMENT_STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'user_id',
        'service_id',
        'student_name',
        'student_email',
        'student_phone',
        'amount',
        'amount_paid',
        'amount_due',
        'payment_method',
        'payment_status',
        'enrollment_status',
        'admin_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Keep the remaining balance consistent even when the form's
        // auto-calculation hasn't run (bulk updates, API writes, etc.).
        static::saving(function (ServiceOrder $order): void {
            if ($order->amount !== null && $order->amount_paid !== null) {
                $order->amount_due = max(0, (float) $order->amount - (float) $order->amount_paid);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
