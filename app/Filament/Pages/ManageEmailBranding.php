<?php

namespace App\Filament\Pages;

use App\Models\EmailBranding;
use App\Services\EmailService;
use App\Support\EmailShell;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;
use UnitEnum;

/**
 * Centralized management UI for the visual/design layer of every branded
 * email. Edits write to the single EmailBranding row (id = 1) and take effect
 * on the very next email composed through EmailService → EmailShell.
 *
 * The live preview renders through the exact delivery seam
 * (EmailService::renderForDelivery → EmailShell → emails.layouts.branded) with
 * the unsaved form state as shell overrides, so what an admin previews is what
 * recipients receive once the row is saved.
 */
class ManageEmailBranding extends Page
{
    /**
     * The columns editable from this page (schema fields mirror this list).
     */
    public const BRANDING_FIELDS = [
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

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'Email Branding';

    protected static ?string $title = 'Email Branding';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-email-branding';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Communication';
    }

    public function mount(): void
    {
        // Ensure the singleton row exists so the form always has editable
        // defaults (colors etc.), then show the stored values.
        $this->form->fill(Arr::only(EmailBranding::ensureExists()->toArray(), self::BRANDING_FIELDS));
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Status')
                    ->description('When disabled, every branded email falls back to the built-in default design (original Banglay Chinese branding).')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Use this email branding')
                            ->default(true)
                            ->live(),
                    ]),

                Section::make('Logo')
                    ->description('Shown in the email header and linked to the website. Applies to every branded email at once.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Email logo')
                            ->disk('public')
                            ->directory('email-branding')
                            ->image()
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/png', 'image/jpeg'])
                            ->visibility('public')
                            ->live()
                            ->helperText('PNG is recommended — it is the most widely supported image format in email clients. JPEG is accepted too. Leave empty to use the site logo from Settings, then the bundled PNG. Recommended ~360px wide. Max 5 MB.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Brand Information')
                    ->description('Identity shown in the header and footer of every branded email.')
                    ->columns(2)
                    ->schema([
                        static::textField('app_name')
                            ->label('App / company name')
                            ->placeholder('Banglay Chinese')
                            ->maxLength(255)
                            ->helperText('Leave empty to use the site name from Settings.')
                            ->columnSpan(1),
                        static::textField('tagline')
                            ->label('Tagline')
                            ->placeholder('Learn Chinese in Bangla • Study in China')
                            ->maxLength(255)
                            ->helperText('Shown under the logo and repeated in the footer band. Leave empty for the built-in tagline.')
                            ->columnSpan(1),
                    ]),

                Section::make('Colors')
                    ->description('Used by the email shell (header tagline, top accent strip, footer band, message and note text). Template-specific colors inside individual email bodies are content, not branding, and stay untouched.')
                    ->columns(2)
                    ->schema([
                        static::colorField('primary_color', 'Primary brand color', 'Header tagline, top accent strip. Default #007A3D.'),
                        static::colorField('accent_color', 'Accent / footer color', 'Deep footer band behind the contact & social links. Keep it dark for readable white text. Default #004D26.'),
                        static::colorField('body_text_color', 'Body text color', 'Main message text on the white card. Default #1E293B.'),
                        static::colorField('muted_text_color', 'Muted text color', 'Quiet footnote / helper text. Default #64748B.'),
                    ]),

                Section::make('Contact & WhatsApp')
                    ->description('Reachable contact details. The WhatsApp link is generated automatically from the country code and number — you never type a wa.me URL.')
                    ->columns(2)
                    ->schema([
                        static::textField('contact_email')
                            ->label('Contact email')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Shown in the post-script row. Leave empty to use the site contact email from Settings.')
                            ->columnSpan(1),
                        static::textField('contact_phone')
                            ->label('Phone number (optional)')
                            ->maxLength(40)
                            ->helperText('Shown after the contact email in the post-script row. Leave empty to hide it.')
                            ->columnSpan(1),
                        static::textField('whatsapp_country_code')
                            ->label('WhatsApp country code')
                            ->placeholder('+880')
                            ->maxLength(5)
                            ->rule('regex:/^\+?[0-9]{1,4}$/')
                            ->helperText('e.g. +880 or 880. The "+" is only for display — the generated link contains digits only. Leave empty together with the number to hide WhatsApp.')
                            ->columnSpan(1),
                        static::textField('whatsapp_number')
                            ->label('WhatsApp number')
                            ->placeholder('1712 345 678')
                            ->maxLength(24)
                            ->rule('regex:/^[0-9\s()+\-]{6,24}$/')
                            ->helperText('Number without the country code — spaces, hyphens and parentheses are fine and are normalized. Leave empty to hide WhatsApp.')
                            ->columnSpan(1),
                        static::textField('website_url')
                            ->label('Website URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Logo and footer "Website" link destination. Leave empty to use the app URL.')
                            ->columnSpan(2),
                    ]),

                Section::make('Social Links')
                    ->description('Optional — each network appears in the email footer only when a valid https:// URL is set.')
                    ->columns(2)
                    ->schema([
                        static::socialUrlField('facebook_url', 'Facebook URL', 'https://facebook.com/yourpage'),
                        static::socialUrlField('instagram_url', 'Instagram URL', 'https://instagram.com/yourhandle'),
                        static::socialUrlField('youtube_url', 'YouTube URL', 'https://youtube.com/@yourchannel'),
                    ]),

                Section::make('Footer')
                    ->description('Optional text shown in the footer band of every branded email.')
                    ->columns(2)
                    ->schema([
                        Textarea::make('footer_text')
                            ->label('Footer message (optional)')
                            ->rows(2)
                            ->maxLength(500)
                            ->live(debounce: 500)
                            ->helperText('A short plain-text line inside the footer band above the copyright, e.g. "Made with ❤️ in Beijing & Dhaka". Leave empty to hide it.')
                            ->columnSpanFull(),
                        static::textField('copyright_text')
                            ->label('Copyright text')
                            ->placeholder('© {year} {site}. All rights reserved.')
                            ->maxLength(255)
                            ->helperText('Leave empty to render "© {year} {app name}. All rights reserved." with the current year.')
                            ->columnSpan(2),
                    ]),
            ]);
    }

    /**
     * Optional social URL field: must be a valid https:// URL or empty.
     */
    protected static function socialUrlField(string $key, string $label, string $placeholder): TextInput
    {
        return static::textField($key)
            ->label($label)
            ->placeholder($placeholder)
            ->url()
            ->maxLength(255)
            ->rule('regex:/^https:\/\//i')
            ->helperText('Full https:// URL. Leave empty to hide this link from emails.')
            ->columnSpan(1);
    }

    /**
     * Text inputs on this page are live (debounced) so the unsaved form state
     * reaches the server and the preview reflects it while typing.
     */
    protected static function textField(string $key): TextInput
    {
        return TextInput::make($key)->live(debounce: 500);
    }

    protected static function colorField(string $key, string $label, string $helper): ColorPicker
    {
        return ColorPicker::make($key)
            ->label($label)
            ->hex()
            ->required()
            ->live()
            ->rule('regex:/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/')
            ->helperText($helper)
            ->columnSpan(1);
    }

    /**
     * Persist the form state onto the singleton EmailBranding row.
     */
    public function save(): void
    {
        $data = $this->form->getState();

        $row = EmailBranding::ensureExists();

        foreach ($data as $key => $value) {
            if (! in_array($key, self::BRANDING_FIELDS, true)) {
                continue;
            }

            $row->{$key} = $key === 'is_active'
                ? (bool) $value
                : trim((string) (is_array($value) ? ($value[0] ?? '') : ($value ?? '')));
        }

        $row->save();

        Notification::make()
            ->success()
            ->title('Email branding saved.')
            ->body('Every branded email now uses the updated branding.')
            ->send();
    }

    /**
     * Livewire-visible preview: renders the current (possibly unsaved) form
     * state through the canonical EmailShell so admins see the actual email.
     */
    public function renderPreview(): string
    {
        return static::previewHtml($this->data ?? []);
    }

    /**
     * The origin the browser can actually reach the logo from. Delivery keeps
     * using EmailShell::origin() (production-safe, never localhost); the
     * preview, running inside the admin browser, must point uploaded logos at
     * the origin that serves this request (localhost in local development,
     * the real domain in production).
     */
    public static function previewAssetOrigin(): string
    {
        $request = request();

        try {
            if ($request !== null && filled($request->getHttpHost())) {
                return $request->getScheme().'://'.$request->getHttpHost();
            }
        } catch (Throwable) {
            // No usable HTTP host (e.g. console/tests without a real request):
            // fall back to the same production-safe origin delivery uses.
        }

        return EmailShell::origin();
    }

    /**
     * Compose the exact branded document a real send would deliver for the
     * given branding values (the Email Branding form state). Goes through the
     * same path as delivery — EmailService::renderForDelivery → EmailShell →
     * emails.layouts.branded — with the form values supplied as overrides.
     *
     * @param  array<string, mixed>  $data
     */
    public static function previewHtml(array $data): string
    {
        $mapped = [];

        foreach (self::BRANDING_FIELDS as $key) {
            $value = $data[$key] ?? EmailBranding::defaultAttributes()[$key];
            $mapped[$key] = is_array($value) ? ($value[0] ?? '') : $value;
        }

        // A freshly selected (not yet stored) file is a temporary upload
        // object, not a usable path yet — treat it as empty so the preview
        // falls back to the bundled logo exactly like delivery would.
        $logo = is_string($mapped['logo'] ?? null) ? trim($mapped['logo']) : '';

        if (filled($logo) && ! preg_match('~^https?://~i', $logo) && ! Storage::disk('public')->exists($logo)) {
            $logo = '';
        }

        $mapped['logo'] = $logo;

        $branding = (bool) ($mapped['is_active'] ?? true)
            ? new EmailBranding($mapped)
            : new EmailBranding(EmailBranding::defaultAttributes());

        $variables = EmailShell::brandingVariables($branding, static::previewAssetOrigin());

        return app(EmailService::class)->renderForDelivery(
            static::sampleContent($variables),
            $variables,
        );
    }

    /**
     * Representative content fragment for the preview — exercises the heading,
     * body, CTA pill, muted helper note and boxed info that real template
     * bodies use, colored with the current branding.
     *
     * @param  array<string, string>  $variables
     */
    protected static function sampleContent(array $variables): string
    {
        $primary = $variables['primaryColor'];
        $muted = $variables['mutedTextColor'];
        $bodyText = $variables['bodyTextColor'];

        return '<h2 style="margin:0 0 18px;font-family:\'Poppins\',\'Hind Siliguri\',Arial,sans-serif;font-size:24px;font-weight:700;line-height:1.4;color:'.$primary.';">Welcome to Banglay Chinese!</h2>'
            .'<p style="margin:0 0 16px;line-height:1.7;">This is a sample of how your branded emails will look. Your logo, tagline, colors, footer and contact details appear exactly as they will for recipients.</p>'
            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0;"><tr><td style="background-color:#E6F4EA;border-left:4px solid '.$primary.';border-radius:8px;padding:14px 18px;font-size:14px;line-height:1.8;color:#004D26;">Sample call-to-action button below — colored with your primary color.</td></tr></table>'
            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0;"><tr><td align="center"><table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" bgcolor="'.$primary.'" style="border-radius:999px;background-color:'.$primary.';"><a href="#preview" style="display:inline-block;padding:14px 30px;border-radius:999px;background-color:'.$primary.';color:#FFFFFF;font-family:\'Poppins\',\'Hind Siliguri\',Arial,sans-serif;font-size:15px;font-weight:700;line-height:1.3;text-decoration:none;">Sample CTA Button</a></td></tr></table></td></tr></table>'
            .'<p style="margin:0;font-size:13px;color:'.$muted.';line-height:1.6;">Muted helper text is styled with your muted color, and the main copy above uses your body text color ('.$bodyText.').</p>';
    }
}
