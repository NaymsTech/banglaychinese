<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DigitalOrder extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_NEEDS_ATTENTION = 'needs_attention';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_NEEDS_ATTENTION => 'Needs Attention',
    ];

    protected $fillable = [
        'user_id',
        'product_id',
        'student_name',
        'student_email',
        'student_phone',
        'trx_id',
        'rejection_reason',
        'attention_reason',
        'amount',
        'status',
        'approval_email_sent_at',
        'order_received_email_sent_at',
        'rejection_email_sent_at',
        'attention_email_sent_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approval_email_sent_at' => 'datetime',
        'order_received_email_sent_at' => 'datetime',
        'rejection_email_sent_at' => 'datetime',
        'attention_email_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unifiedOrder(): HasOne
    {
        return $this->hasOne(Order::class, 'legacy_id')->where('legacy_source', Order::SOURCE_DIGITAL_ORDER);
    }
}
