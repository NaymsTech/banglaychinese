<?php

namespace App\Filament\Resources\EmailProviders\Schemas;

use App\Models\EmailProvider;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EmailProviderForm
{
    /**
     * The config keys that hold credentials for each driver. These fields
     * never pre-fill from the stored value and only overwrite it when the
     * admin types a replacement, so credentials stay out of the UI.
     */
    public static function secretConfigKeysForDriver(string $driver): array
    {
        return match ($driver) {
            EmailProvider::DRIVER_SMTP => ['password'],
            EmailProvider::DRIVER_SENDGRID => ['api_key'],
            default => [],
        };
    }

    /**
     * When a secret field is left blank on save, carry the currently stored
     * value over instead of dropping it from the rewritten config payload.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public static function preserveExistingSecrets(?EmailProvider $record, array $config): array
    {
        if ($record === null) {
            return $config;
        }

        foreach (self::secretConfigKeysForDriver($config['driver'] ?? $record->driver) as $key) {
            $existing = $record->config[$key] ?? null;

            if (filled($existing) && blank($config[$key] ?? null)) {
                $config[$key] = $existing;
            }
        }

        return $config;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->description('Identity and sending behaviour of this provider.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Provider name')
                            ->placeholder('e.g. Brevo, SendGrid')
                            ->required()
                            ->maxLength(255)
                            ->unique(table: EmailProvider::class, column: 'name', ignoreRecord: true),
                        Select::make('driver')
                            ->label('Driver')
                            ->options(EmailProvider::DRIVERS)
                            ->default(EmailProvider::DRIVER_SMTP)
                            ->required()
                            ->live(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive providers are skipped when sending.'),
                        TextInput::make('priority')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->helperText('Lower number = higher priority. Tried first when sending.'),
                        TextInput::make('daily_limit')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('0 = unlimited. Counts resets each day.'),
                    ]),

                Section::make('Sender Identity')
                    ->description('Used as the “From” address for every email sent through this provider.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('config.from_address')
                            ->label('From address')
                            ->email()
                            ->placeholder('info@banglaychinese.com')
                            ->maxLength(255),
                        TextInput::make('config.from_name')
                            ->label('From name')
                            ->placeholder('Banglay Chinese')
                            ->maxLength(255),
                    ]),

                Section::make('SMTP Configuration')
                    ->description('Host and credentials used when the driver is SMTP (Brevo, SendGrid via SMTP, etc.).')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('driver') === EmailProvider::DRIVER_SMTP)
                    ->schema([
                        TextInput::make('config.host')
                            ->label('Host')
                            ->placeholder('smtp-relay.brevo.com')
                            ->maxLength(255),
                        TextInput::make('config.port')
                            ->label('Port')
                            ->numeric()
                            ->placeholder('587'),
                        Select::make('config.encryption')
                            ->label('Encryption')
                            ->options([
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                                'none' => 'None',
                            ])
                            ->default('tls'),
                        TextInput::make('config.username')
                            ->label('Username')
                            ->maxLength(255),
                        TextInput::make('config.password')
                            ->label('Password / SMTP key')
                            ->password()
                            ->afterStateHydrated(fn (TextInput $component) => $component->state(''))
                            ->dehydrated(fn (Get $get): bool => filled($get('config.password')))
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'The SMTP password for this account.'
                                : 'Leave blank to keep the current key — it is never shown again.'),
                    ]),

                Section::make('API Credentials')
                    ->description('Key used by providers that send through their HTTP API.')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('driver') === EmailProvider::DRIVER_SENDGRID)
                    ->schema([
                        TextInput::make('config.api_key')
                            ->label('API key')
                            ->password()
                            ->afterStateHydrated(fn (TextInput $component) => $component->state(''))
                            ->dehydrated(fn (Get $get): bool => filled($get('config.api_key')))
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'The SendGrid API key.'
                                : 'Leave blank to keep the current key — it is never shown again.'),
                    ]),
            ]);
    }
}
