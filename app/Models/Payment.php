<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHOD_BKASH = 'bkash';

    public const METHOD_NAGAD = 'nagad';

    public const METHOD_BANK = 'bank';

    public const METHOD_CASH = 'cash';

    public const METHOD_FREE = 'free';

    public const METHOD_OTHER = 'other';

    public const METHODS = [
        self::METHOD_BKASH => 'bKash',
        self::METHOD_NAGAD => 'Nagad',
        self::METHOD_BANK => 'Bank Transfer',
        self::METHOD_CASH => 'Cash',
        self::METHOD_FREE => 'Free',
        self::METHOD_OTHER => 'Other',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_NEEDS_ATTENTION = 'needs_attention';

    public const STATUS_PAID = 'paid';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_NEEDS_ATTENTION => 'Needs Attention',
        self::STATUS_PAID => 'Paid',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_REFUNDED => 'Refunded',
    ];

    protected $fillable = [
        'order_id',
        'method',
        'trx_reference',
        'sender_number',
        'sender_details',
        'amount',
        'currency',
        'status',
        'review_note',
        'gateway',
        'gateway_ref',
        'gateway_raw_status',
        'callback_payload',
        'proof_path',
        'paid_at',
        'rejected_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_payload' => 'array',
        'paid_at' => 'datetime',
        'rejected_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
