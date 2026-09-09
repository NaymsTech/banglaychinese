<?php

namespace App\Filament\Resources\EmailProviders\Tables;

use App\Models\EmailProvider;
use App\Services\EmailService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class EmailProvidersTable
{
    protected static function driverColor(string $driver): string
    {
        return match ($driver) {
            EmailProvider::DRIVER_SMTP => 'info',
            EmailProvider::DRIVER_SENDGRID => 'success',
            default => 'gray',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('driver')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => EmailProvider::DRIVERS[$state] ?? $state)
                    ->color(fn (string $state): string => self::driverColor($state)),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger')
                    ->tooltip('Enable / disable this provider'),
                TextColumn::make('priority')
                    ->sortable(),
                TextColumn::make('sent_today')
                    ->label('Sent / Limit')
                    ->formatStateUsing(fn (EmailProvider $record): string => $record->daily_limit > 0
                        ? "{$record->sent_today} / {$record->daily_limit}"
                        : "{$record->sent_today} / Unlimited")
                    ->tooltip('Emails sent today against the daily limit'),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('testConnection')
                    ->label('Test connection')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Test provider connection')
                    ->modalDescription(fn (EmailProvider $record): string => "A test email will be sent to your address using “{$record->name}”.")
                    ->action(function (EmailProvider $record): void {
                        $result = app(EmailService::class)->testConnection($record, auth()->user()?->email);

                        if ($result['success']) {
                            Notification::make()
                                ->success()
                                ->title($result['message'])
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->danger()
                            ->title('Test email failed.')
                            ->body($result['message'])
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete this provider?')
                    ->modalDescription('Past email logs keep their history — only the provider record is removed.'),
            ])
            ->defaultSort('priority');
    }
}
