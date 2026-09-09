<?php

namespace App\Support;

use App\Models\EmailBranding;
use App\Services\SettingsService;

/**
 * The single reusable presentation shell for every Banglay Chinese email.
 *
 * Database templates keep storing bare content fragments (raw HTML with
 * {placeholders}); EmailService wraps that content in the branded layout at
 * the point a message is composed for a provider. Complete HTML documents
 * (the auth Blade fallbacks, legacy full-document template rows, and any
 * already-wrapped payload that is ever resent) pass through untouched so
 * nothing is ever nested inside a second <html> document.
 *
 * Branding is never hardcoded here: every design variable (logo, site name,
 * tagline, colours, footer/contact details) is resolved from the centralized
 * EmailBranding configuration through brandingVariables(), with safe built-in
 * defaults that reproduce the original branded design when the configuration
 * row is missing or disabled. EmailBranding rows are read at render time, so
 * a branding change applies to the very next email that is composed.
 *
 * All URLs returned here are absolute and production-safe. config('app.url')
 * is honoured, but a localhost/empty origin falls back to the production
 * domain so queued sends can never embed a localhost logo link.
 */
class EmailShell
{
    /**
     * The production origin used when APP_URL is unset or points at localhost.
     */
    public const PRODUCTION_ORIGIN = 'https://banglaychinese.com';

    /**
     * Resolve the site origin for absolute email URLs.
     */
    public static function origin(): string
    {
        $configured = rtrim((string) config('app.url', self::PRODUCTION_ORIGIN), '/');

        $host = (string) parse_url($configured, PHP_URL_HOST);

        if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '[::1]', '::1'], true)) {
            return self::PRODUCTION_ORIGIN;
        }

        return $configured;
    }

    /**
     * Absolute URL of the brand logo shown in the email header.
     *
     * The explicit email-branding logo (stored on the public disk or given as
     * an absolute URL) wins; otherwise an admin-uploaded site logo (also on
     * the public disk) is honoured, mirroring the header/footer resolution in
     * resources/views/layouts/app.blade.php. Empty finally falls back to the
     * bundled PNG asset, so an email always carries a reachable logo.
     *
     * $assetOrigin overrides the origin that serves the logo/storage URLs.
     * Delivery never passes one (so emails always embed the production-safe
     * origin from origin()); the Filament preview passes the origin the admin
     * is currently browsing so an uploaded logo is reachable in the preview.
     */
    public static function logoUrl(?string $logo = null, ?string $assetOrigin = null): string
    {
        $path = trim((string) ($logo ?? EmailBranding::source()->logo ?? ''));

        if ($path === '') {
            $path = trim((string) SettingsService::get('site_logo', ''));
        }

        $origin = $assetOrigin ?? self::origin();

        if ($path === '') {
            return $origin.'/assets/logo-full.png';
        }

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        return $origin.'/storage/'.ltrim($path, '/');
    }

    /**
     * Whether the given HTML is already a complete email document and must
     * therefore not be wrapped in the branded shell again.
     */
    public static function isCompleteEmail(string $html): bool
    {
        return preg_match('/^\s*(?:<!doctype\s+html[^>]*>)?\s*<html(?:\s|>)/i', $html) === 1;
    }

    /**
     * Render the branded email shell around raw content HTML.
     *
     * @param  array<string, mixed>  $data  overrides for any shell variable
     *                                      (see brandingVariables for the keys)
     */
    public static function render(string $content, array $data = []): string
    {
        return view('emails.layouts.branded', array_merge(['content' => $content], $data))->render();
    }

    /**
     * Resolve every shell design variable from the current EmailBranding
     * configuration (or from the given branding source, used by the Filament
     * preview to mirror unsaved form state). Blank branding values inherit an
     * existing site setting or a built-in default, so the values returned here
     * are exactly what a real delivery renders. Social/WhatsApp values are
     * empty strings when nothing is configured, which the layout uses to hide
     * those footer links.
     *
     * $assetOrigin lets the Filament preview resolve the logo against the
     * origin the admin is currently browsing; delivery passes none and keeps
     * the production-safe origin().
     *
     * @return array<string, string>
     */
    public static function brandingVariables(?EmailBranding $branding = null, ?string $assetOrigin = null): array
    {
        $branding ??= EmailBranding::source();

        return [
            'siteName' => $branding->effectiveAppName(),
            'tagline' => $branding->effectiveTagline(),
            'websiteUrl' => filled($branding->website_url) ? trim($branding->website_url) : self::origin(),
            'logoUrl' => self::logoUrl($branding->logo, $assetOrigin),
            'primaryColor' => $branding->effectivePrimaryColor(),
            'accentColor' => $branding->effectiveAccentColor(),
            'bodyTextColor' => $branding->effectiveBodyTextColor(),
            'mutedTextColor' => $branding->effectiveMutedTextColor(),
            'footerText' => $branding->effectiveFooterText(),
            'contactEmail' => $branding->effectiveContactEmail(),
            'contactPhone' => $branding->effectiveContactPhone(),
            'whatsappUrl' => $branding->effectiveWhatsappUrl(),
            'facebookUrl' => $branding->effectiveFacebookUrl(),
            'instagramUrl' => $branding->effectiveInstagramUrl(),
            'youtubeUrl' => $branding->effectiveYoutubeUrl(),
            'copyright' => filled($branding->copyright_text)
                ? trim($branding->copyright_text)
                : '© '.date('Y').' '.$branding->effectiveAppName().'. All rights reserved.',
        ];
    }
}
