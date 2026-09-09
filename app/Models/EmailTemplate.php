<?php

namespace App\Models;

use Database\Factories\EmailTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    /** @use HasFactory<EmailTemplateFactory> */
    use HasFactory;

    public const CATEGORY_TRANSACTIONAL = 'transactional';

    public const CATEGORY_MARKETING = 'marketing';

    public const CATEGORY_REMINDER = 'reminder';

    public const CATEGORY_NOTIFICATION = 'notification';

    public const CATEGORIES = [
        self::CATEGORY_TRANSACTIONAL => 'Transactional',
        self::CATEGORY_MARKETING => 'Marketing',
        self::CATEGORY_REMINDER => 'Reminder',
        self::CATEGORY_NOTIFICATION => 'Notification',
    ];

    /**
     * System-critical templates. These must never be deleted or deactivated,
     * otherwise authentication flows (verify / reset) would silently stop
     * working. The Filament UI protects them and EmailService falls back to
     * the bundled default with a critical log if the row is ever missing.
     */
    public const REQUIRED_KEYS = [
        'email_verification',
        'password_reset',
    ];

    protected $fillable = [
        'name',
        'key',
        'description',
        'subject',
        'body',
        'from_address',
        'from_name',
        'variables',
        'category',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function isRequired(): bool
    {
        return in_array($this->key, self::REQUIRED_KEYS, true);
    }
}
