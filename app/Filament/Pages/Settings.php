<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Settings';
    }

    protected string $view = 'filament.pages.settings';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::pluck('value', 'key')->toArray());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General Info')
                    ->description('Site identity — shown in the header, footer and browser tab.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Site name')
                            ->helperText('Used across the layout, footer copyright and page titles.')
                            ->maxLength(255),
                        TextInput::make('site_tagline')
                            ->label('Tagline')
                            ->helperText('Short description shown in the footer and auth pages.')
                            ->maxLength(255),
                        static::imageField('site_logo', 'Site logo', 'Shown in the header & footer. Falls back to the bundled logo when empty. Recommended: square logo with a transparent background (e.g. 512×512px). Max 5 MB.'),
                        static::imageField('site_favicon', 'Favicon', 'Shown in the browser tab. Falls back to the bundled favicon when empty. Recommended: 64×64px square PNG. Max 5 MB.'),
                    ]),

                Section::make('Contact Info')
                    ->description('Reachable details used for the wa.me links, mailto links and contact sections across the site.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp number')
                            ->helperText('Full international number without "+" or spaces — e.g. 8618223249514.')
                            ->maxLength(30),
                        TextInput::make('contact_email')
                            ->label('Contact email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('physical_address')
                            ->label('Physical address')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Social Media Links')
                    ->description('Social icons appear in the footer. Leave a URL blank to hide that icon.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('linkedin_url')
                            ->label('LinkedIn URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('twitter_url')
                            ->label('X (Twitter) URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('tiktok_url')
                            ->label('TikTok URL')
                            ->url()
                            ->maxLength(255),
                    ]),

                Section::make('Footer Settings')
                    ->description('Bottom bar text of the footer.')
                    ->schema([
                        TextInput::make('footer_copyright_text')
                            ->label('Copyright text')
                            ->placeholder('Copyright © {year} {site}. All rights reserved.')
                            ->helperText('Use {year} and {site} as placeholders — {year} becomes the current year, {site} the site name.')
                            ->maxLength(255),
                    ]),

                Section::make('Payments')
                    ->description('Merchant numbers shown on the checkout / manual-transfer page.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('bkash_number')
                            ->label('bKash merchant number')
                            ->helperText('Used on the checkout page for manual bKash payment.')
                            ->maxLength(30),
                        TextInput::make('nagad_number')
                            ->label('Nagad merchant number')
                            ->helperText('Used on the checkout page for manual Nagad payment.')
                            ->maxLength(30),
                    ]),

                Section::make('Analytics & SEO')
                    ->description('Tracking snippets injected into every page, and the fallback meta description.')
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextInput::make('google_analytics_id')
                            ->label('GA4 measurement ID')
                            ->helperText('e.g. G-XXXXXXXXXX — leave blank to disable.')
                            ->maxLength(100),
                        TextInput::make('facebook_pixel_id')
                            ->label('Facebook Pixel ID')
                            ->helperText('Leave blank to disable the pixel.')
                            ->maxLength(100),
                        Textarea::make('meta_description')
                            ->label('Fallback meta description')
                            ->helperText('Default description for pages without their own meta description.')
                            ->maxLength(300)
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function imageField(string $name, string $label, string $helper): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->disk('public')
            ->image()
            ->maxSize(5120)
            ->visibility('public')
            ->directory('settings')
            ->helperText($helper)
            ->columnSpanFull();
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            SettingsService::set($key, (string) ($value ?? ''));
        }

        SettingsService::flush();

        Notification::make()
            ->success()
            ->title('Settings saved successfully.')
            ->send();
    }
}
