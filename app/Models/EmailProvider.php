<?php

namespace App\Models;

use App\Casts\EncryptedConfig;
use Database\Factories\EmailProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailProvider extends Model
{
    /** @use HasFactory<EmailProviderFactory> */
    use HasFactory;

    public const DRIVER_SMTP = 'smtp';

    public const DRIVER_SENDGRID = 'sendgrid';

    /**
     * Drivers that can actually deliver email with the installed packages.
     * Resend and Mailgun need HTTP bridges that are not installed, so they
     * are intentionally not offered here.
     */
    public const DRIVERS = [
        self::DRIVER_SMTP => 'SMTP',
        self::DRIVER_SENDGRID => 'SendGrid',
    ];

    /**
     * Config keys that hold credentials and must never surface in the UI,
     * logs or stored error messages.
     */
    public const CONFIG_SECRET_KEYS = [
        'password',
        'api_key',
        'secret',
    ];

    protected $fillable = [
        'name',
        'driver',
        'config',
        'is_active',
        'priority',
        'daily_limit',
        'sent_today',
        'last_reset',
    ];

    protected $casts = [
        'config' => EncryptedConfig::class,
        'is_active' => 'boolean',
        'priority' => 'integer',
        'daily_limit' => 'integer',
        'sent_today' => 'integer',
        'last_reset' => 'date',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * The secret values stored in this provider's configuration.
     *
     * @return array<int, string>
     */
    public function secretConfigValues(): array
    {
        $config = $this->config;

        $values = [];

        foreach (self::CONFIG_SECRET_KEYS as $key) {
            $value = $config[$key] ?? null;

            if (is_string($value) && $value !== '') {
                $values[] = $value;
            }
        }

        return array_values(array_unique($values));
    }

    /**
     * Replace every occurrence of this provider's credentials in a message
     * with a placeholder, so errors can be logged or stored safely.
     */
    public function redactSecrets(?string $message): ?string
    {
        if ($message === null || $message === '') {
            return $message;
        }

        foreach ($this->secretConfigValues() as $secret) {
            $message = str_replace($secret, '[REDACTED]', $message);
        }

        return $message;
    }
}
