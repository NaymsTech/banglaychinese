<?php

namespace App\Filament\Resources\EmailLogs\Tables;

use App\Models\EmailLog;
use App\Services\EmailService;
use App\Services\EmergencyEmailQueueProcessor;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailLogsTable
{
    protected static function statusColor(string $status): string
    {
        return match ($status) {
            EmailLog::STATUS_SENT => 'success',
            EmailLog::STATUS_FAILED => 'danger',
            EmailLog::STATUS_QUEUED => 'warning',
            EmailLog::STATUS_SENDING => 'info',
            default => 'gray',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recipient_email')
                    ->label('Recipient')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-envelope'),
                TextColumn::make('subject')
                    ->limit(55)
                    ->tooltip(fn (EmailLog $record): ?string => $record->subject)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('provider.name')
                    ->label('Provider')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('template_key')
                    ->label('Template')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => EmailLog::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => self::statusColor($state)),
                TextColumn::make('attempt_count')
                    ->label('Attempts')
                    ->badge()
                    ->color(fn (EmailLog $record): string => $record->attempt_count > 1 ? 'warning' : 'gray')
                    ->toggleable(),
                TextColumn::make('error_message')
                    ->label('Error')
                    ->placeholder('—')
                    ->limit(45)
                    ->color('danger')
                    ->copyable()
                    ->copyMessage('Error copied to clipboard')
                    ->tooltip(fn (EmailLog $record): ?string => $record->status === EmailLog::STATUS_FAILED ? $record->error_message : null)
                    ->formatStateUsing(fn (?string $state, EmailLog $record): ?string => $record->status === EmailLog::STATUS_FAILED ? $state : null)
                    ->toggleable(),
                TextColumn::make('when')
                    ->label('When')
                    ->state(fn (EmailLog $record): mixed => $record->statusEventTime())
                    ->dateTime('d M Y, g:i A')
                    ->placeholder('—')
                    ->tooltip(fn (EmailLog $record): string => match ($record->status) {
                        EmailLog::STATUS_QUEUED => 'Queued',
                        EmailLog::STATUS_SENDING => 'Sending started',
                        EmailLog::STATUS_SENT => 'Sent',
                        EmailLog::STATUS_FAILED => 'Failed',
                        default => '',
                    }),
                TextColumn::make('message_id')
                    ->label('Message ID')
                    ->limit(25)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Logged')
                    ->dateTime('d M Y, g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(EmailLog::STATUSES),
                SelectFilter::make('provider')
                    ->label('Provider')
                    ->relationship('provider', 'name'),
                SelectFilter::make('template_key')
                    ->label('Template')
                    ->options(fn (): array => EmailLog::query()
                        ->whereNotNull('template_key')
                        ->distinct()
                        ->orderBy('template_key')
                        ->pluck('template_key', 'template_key')
                        ->all()),
                Filter::make('queued_at_range')
                    ->label('Queued between')
                    ->schema([
                        DatePicker::make('queued_from')
                            ->label('From'),
                        DatePicker::make('queued_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['queued_from'] ?? null,
                                fn (Builder $query, string $from): Builder => $query->whereDate('queued_at', '>=', $from),
                            )
                            ->when(
                                $data['queued_until'] ?? null,
                                fn (Builder $query, string $until): Builder => $query->whereDate('queued_at', '<=', $until),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (EmailLog $record): string => 'Email to '.$record->recipient_email)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->schema(fn (EmailLog $record): array => self::detailSchema($record)),
                Action::make('processNow')
                    ->label('Process Now')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn (EmailLog $record): bool => $record->status === EmailLog::STATUS_QUEUED)
                    ->requiresConfirmation()
                    ->modalHeading('Process this email now?')
                    ->modalDescription('This manually processes this specific queued email now. Use it only as an emergency fallback when the normal queue worker is unavailable — it does not create a duplicate delivery.')
                    ->modalSubmitActionLabel('Process now')
                    ->action(function (EmailLog $record): void {
                        $result = app(EmergencyEmailQueueProcessor::class)->processQueuedEmail($record);

                        if (! $result['claimed']) {
                            Notification::make()
                                ->info()
                                ->title('Email already being processed')
                                ->body('This email is already being processed or is no longer queued.')
                                ->send();

                            return;
                        }

                        if ($record->fresh()?->status === EmailLog::STATUS_SENT) {
                            Notification::make()
                                ->success()
                                ->title('Email processed successfully')
                                ->body('The email was delivered through the normal email pipeline.')
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->danger()
                            ->title('Email could not be processed')
                            ->body('The delivery attempt failed. Check the Email Log entry for details.')
                            ->send();
                    }),
                Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (EmailLog $record): bool => $record->canBeResent())
                    ->requiresConfirmation()
                    ->modalHeading('Resend this email?')
                    ->modalDescription('A new delivery attempt will be queued as a separate log entry. The original failed record stays for the audit trail.')
                    ->modalSubmitActionLabel('Queue resend')
                    ->action(function (EmailLog $record): void {
                        $result = app(EmailService::class)->resend($record);

                        if ($result['success'] ?? false) {
                            Notification::make()
                                ->success()
                                ->title('Resend queued')
                                ->body("A new delivery attempt was queued for {$record->recipient_email}.")
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->danger()
                            ->title('Resend failed')
                            ->body($result['error'] ?? 'The email could not be re-queued.')
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * @return array<int, object>
     */
    private static function detailSchema(EmailLog $record): array
    {
        return [
            Section::make('Recipient')
                ->columns(2)
                ->schema([
                    TextEntry::make('recipient_email')
                        ->label('Email')
                        ->copyable()
                        ->columnSpanFull(),
                    TextEntry::make('recipient_name')
                        ->placeholder('—'),
                    TextEntry::make('from')
                        ->label('From')
                        ->state(fn (EmailLog $record): string => filled($record->from_address)
                            ? trim(($record->from_name ?? '').' <'.$record->from_address.'>')
                            : 'Provider default')
                        ->columnSpanFull(),
                    TextEntry::make('provider.name')
                        ->label('Provider')
                        ->placeholder('Not yet assigned'),
                    TextEntry::make('template_key')
                        ->label('Template')
                        ->placeholder('—'),
                ]),
            Section::make('Delivery')
                ->columns(2)
                ->schema([
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => EmailLog::STATUSES[$state] ?? $state)
                        ->color(fn (string $state): string => self::statusColor($state)),
                    TextEntry::make('attempt_count')
                        ->label('Attempts'),
                    TextEntry::make('message_id')
                        ->label('Message ID')
                        ->copyable()
                        ->placeholder('—'),
                    TextEntry::make('queued_at')
                        ->label('Queued')
                        ->dateTime('d M Y, g:i A')
                        ->placeholder('—'),
                    TextEntry::make('sending_at')
                        ->label('Sending started')
                        ->dateTime('d M Y, g:i A')
                        ->placeholder('—'),
                    TextEntry::make('sent_at')
                        ->label('Sent')
                        ->dateTime('d M Y, g:i A')
                        ->placeholder('—'),
                    TextEntry::make('failed_at')
                        ->label('Failed')
                        ->dateTime('d M Y, g:i A')
                        ->placeholder('—'),
                    TextEntry::make('error_message')
                        ->label('Error')
                        ->placeholder('—')
                        ->color('danger')
                        ->copyable()
                        ->visible(fn (EmailLog $record): bool => $record->status === EmailLog::STATUS_FAILED)
                        ->columnSpanFull(),
                ]),
            Section::make('Content')
                ->schema([
                    TextEntry::make('subject')
                        ->label('Subject')
                        ->columnSpanFull(),
                    TextEntry::make('body')
                        ->label('Body')
                        ->html()
                        ->placeholder('No body stored')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
