<?php

namespace App\Models;

use Database\Factories\EmailLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class EmailLog extends Model
{
    /** @use HasFactory<EmailLogFactory> */
    use HasFactory;

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_QUEUED => 'Queued',
        self::STATUS_SENDING => 'Sending',
        self::STATUS_SENT => 'Sent',
        self::STATUS_FAILED => 'Failed',
    ];

    protected $fillable = [
        'provider_id',
        'template_key',
        'recipient_email',
        'recipient_name',
        'subject',
        'from_address',
        'from_name',
        'body',
        'status',
        'error_message',
        'queued_at',
        'sending_at',
        'sent_at',
        'failed_at',
        'attempt_count',
        'message_id',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sending_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'attempt_count' => 'integer',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(EmailProvider::class);
    }

    /**
     * Whether this row can be safely re-queued by the admin “resend” action.
     * Only terminal failures with a stored body qualify, so a successful
     * email can never be duplicated by accident.
     */
    public function canBeResent(): bool
    {
        return $this->status === self::STATUS_FAILED && filled($this->body);
    }

    /**
     * The datetime this row reached its current state, if one was recorded.
     */
    public function statusEventTime(): ?Carbon
    {
        return $this->{match ($this->status) {
            self::STATUS_QUEUED => 'queued_at',
            self::STATUS_SENDING => 'sending_at',
            self::STATUS_SENT => 'sent_at',
            self::STATUS_FAILED => 'failed_at',
            default => 'updated_at',
        }};
    }
}
