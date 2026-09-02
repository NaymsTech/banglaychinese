<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Settings extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

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
                Section::make('General')
                    ->columns(2)
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Site name')
                            ->maxLength(255),
                        FileUpload::make('site_logo')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('settings')
                            ->maxSize(1024),
                        TextInput::make('site_tagline')
                            ->label('Tagline')
                            ->maxLength(255),
                        TextInput::make('contact_email')
                            ->label('Contact email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('physical_address')
                            ->label('Physical address')
                            ->maxLength(500),
                    ]),

                Section::make('Social Media')
                    ->columns(2)
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp number')
                            ->helperText('Used for the wa.me links across the site.')
                            ->maxLength(30),
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('linkedin_url')
                            ->label('LinkedIn URL')
                            ->url()
                            ->maxLength(255),
                    ]),

                Section::make('Payments')
                    ->columns(2)
                    ->schema([
                        TextInput::make('bkash_number')
                            ->label('bKash merchant number')
                            ->maxLength(30),
                        TextInput::make('nagad_number')
                            ->label('Nagad merchant number')
                            ->maxLength(30),
                    ]),

                Section::make('Analytics & SEO')
                    ->columns(2)
                    ->schema([
                        TextInput::make('google_analytics_id')
                            ->label('GA4 measurement ID')
                            ->helperText('e.g. G-XXXXXXXXXX')
                            ->maxLength(100),
                        TextInput::make('facebook_pixel_id')
                            ->label('Facebook Pixel ID')
                            ->maxLength(100),
                        TextInput::make('meta_description')
                            ->label('Meta description')
                            ->helperText('Shown in search results and social shares.')
                            ->maxLength(300)
                            ->columnSpanFull(),
                    ]),
            ]);
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
