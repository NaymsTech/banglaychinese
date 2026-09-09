<?php

namespace App\Models;

use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Model;

/**
 * Centralized email branding — the single source of truth for the visual
 * layer of every branded email.
 *
 * The whole table holds ONE row (id = 1). Field values that are left blank
 * deliberately inherit an existing application setting (site name, contact
 * email, uploaded site logo) or a built-in default, so a fresh install and a
 * pre-branding database render byte-for-byte what they rendered before this
 * configuration existed. When `is_active` is false the row is ignored and the
 * shell falls back to the built-in defaults, matching the pre-branding design.
 *
 * Consumers (EmailShell, the Filament preview and the delivery seam) resolve
 * values through the same methods, so preview and delivery can never diverge.
 */
class EmailBranding extends Model
{
    /**
     * The only row id ever used.
     */
    public const SINGLETON_ID = 1;

    public const DEFAULT_APP_NAME = 'Banglay Chinese';

    public const DEFAULT_TAGLINE = 'Learn Chinese in Bangla • Study in China';

    public const DEFAULT_PRIMARY_COLOR = '#007A3D';

    public const DEFAULT_ACCENT_COLOR = '#004D26';

    public const DEFAULT_BODY_TEXT_COLOR = '#1E293B';

    public const DEFAULT_MUTED_TEXT_COLOR = '#64748B';

    public const DEFAULT_CONTACT_EMAIL = 'info@banglaychinese.com';

    /**
     * The table is intentionally singular: it holds a single configuration row.
     */
    protected $table = 'email_branding';

    protected $fillable = [
        'logo',
        'app_name',
        'tagline',
        'primary_color',
        'accent_color',
        'body_text_color',
        'muted_text_color',
        'footer_text',
        'contact_email',
        'contact_phone',
        'whatsapp_country_code',
        'whatsapp_number',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'website_url',
        'copyright_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Built-in values used to create the row and to render emails when the
     * row is missing or disabled.
     *
     * @return array<string, mixed>
     */
    public static function defaultAttributes(): array
    {
        return [
            'logo' => '',
            'app_name' => '',
            'tagline' => '',
            'primary_color' => self::DEFAULT_PRIMARY_COLOR,
            'accent_color' => self::DEFAULT_ACCENT_COLOR,
            'body_text_color' => self::DEFAULT_BODY_TEXT_COLOR,
            'muted_text_color' => self::DEFAULT_MUTED_TEXT_COLOR,
            'footer_text' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'whatsapp_country_code' => '',
            'whatsapp_number' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'youtube_url' => '',
            'website_url' => '',
            'copyright_text' => '',
            'is_active' => true,
        ];
    }

    /**
     * The persisted singleton row, when one exists.
     */
    public static function currentRow(): ?self
    {
        return static::query()->find(self::SINGLETON_ID);
    }

    /**
     * The branding source for rendering: the persisted row when it exists and
     * is active, otherwise an unpersisted instance holding the built-in
     * defaults (which themselves inherit site settings where a value is
     * blank). Never writes to the database.
     */
    public static function source(): self
    {
        $row = static::currentRow();

        if ($row !== null && $row->is_active) {
            return $row;
        }

        return new static(static::defaultAttributes());
    }

    /**
     * Create the singleton row with built-in defaults when it is missing.
     * Idempotent — an administrator's saved values are never overwritten.
     */
    public static function ensureExists(): self
    {
        return static::query()->firstOrCreate(['id' => self::SINGLETON_ID], static::defaultAttributes());
    }

    /**
     * App/company name shown in the header, footer band and copyright.
     */
    public function effectiveAppName(): string
    {
        return filled($this->app_name)
            ? trim($this->app_name)
            : (string) (SettingsService::get('site_name') ?: self::DEFAULT_APP_NAME);
    }

    /**
     * Tagline under the logo (also repeated inside the footer band).
     */
    public function effectiveTagline(): string
    {
        return filled($this->tagline) ? trim($this->tagline) : self::DEFAULT_TAGLINE;
    }

    public function effectivePrimaryColor(): string
    {
        return filled($this->primary_color) ? trim($this->primary_color) : self::DEFAULT_PRIMARY_COLOR;
    }

    public function effectiveAccentColor(): string
    {
        return filled($this->accent_color) ? trim($this->accent_color) : self::DEFAULT_ACCENT_COLOR;
    }

    public function effectiveBodyTextColor(): string
    {
        return filled($this->body_text_color) ? trim($this->body_text_color) : self::DEFAULT_BODY_TEXT_COLOR;
    }

    public function effectiveMutedTextColor(): string
    {
        return filled($this->muted_text_color) ? trim($this->muted_text_color) : self::DEFAULT_MUTED_TEXT_COLOR;
    }

    /**
     * Quiet footnote line inside the footer band; empty renders nothing.
     */
    public function effectiveFooterText(): string
    {
        return filled($this->footer_text) ? trim($this->footer_text) : '';
    }

    /**
     * Reachable email shown in the post-script row.
     */
    public function effectiveContactEmail(): string
    {
        return filled($this->contact_email)
            ? trim($this->contact_email)
            : (string) (SettingsService::get('contact_email') ?: self::DEFAULT_CONTACT_EMAIL);
    }

    /**
     * Optional phone shown in the post-script row; empty renders nothing.
     */
    public function effectiveContactPhone(): string
    {
        return filled($this->contact_phone) ? trim($this->contact_phone) : '';
    }

    /**
     * Digits only — every formatting character (+ - space parentheses etc.)
     * is dropped so a wa.me link never carries them.
     */
    public static function phoneDigits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    /**
     * Build the digits-only international number for a wa.me link from a
     * country code and a (local or international) number.
     *
     * - "+880" + "1712 345 678"        → "8801712345678"
     * - "880" + "8801712345678"        → "8801712345678" (code not duplicated)
     * - "" + "8618223249514"           → "8618223249514" (already international)
     * - "" + ""                        → "" (nothing to link)
     */
    public static function internationalWhatsAppNumber(?string $countryCode, ?string $number): string
    {
        $code = static::phoneDigits($countryCode);
        $digits = static::phoneDigits($number);

        if ($digits === '') {
            return '';
        }

        if ($code === '' || str_starts_with($digits, $code)) {
            return $digits;
        }

        return $code.$digits;
    }

    /**
     * Canonical clickable WhatsApp link for this branding.
     *
     * When the branding row has no WhatsApp values at all, the site's stored
     * full international number (the legacy single-field setting) is inherited
     * so existing installs keep their link. A blank number never produces a
     * link.
     */
    public function effectiveWhatsappUrl(): string
    {
        $countryCode = trim((string) $this->whatsapp_country_code);
        $number = trim((string) $this->whatsapp_number);

        if ($countryCode === '' && $number === '') {
            $number = (string) SettingsService::get('whatsapp_number', '');
        }

        $international = static::internationalWhatsAppNumber($countryCode, $number);

        return $international === '' ? '' : 'https://wa.me/'.$international;
    }

    /**
     * Facebook page URL shown in the footer when configured.
     */
    public function effectiveFacebookUrl(): string
    {
        return trim((string) ($this->facebook_url ?: SettingsService::get('facebook_url') ?: ''));
    }

    /**
     * Instagram profile URL shown in the footer when configured.
     */
    public function effectiveInstagramUrl(): string
    {
        return trim((string) ($this->instagram_url ?: SettingsService::get('instagram_url') ?: ''));
    }

    /**
     * YouTube channel URL shown in the footer when configured.
     */
    public function effectiveYoutubeUrl(): string
    {
        return trim((string) ($this->youtube_url ?: SettingsService::get('youtube_url') ?: ''));
    }
}
