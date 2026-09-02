<?php

namespace App\Filament\Resources\Enrollments\Tables;

use App\Models\Enrollment;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnrollmentsTable
{
    public static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'active' => 'Approved',
            'cancelled' => 'Rejected',
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state),
                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state === 'bkash' ? 'bKash' : 'Nagad')
                    ->color(fn ($state): string => $state === 'bkash' ? 'pink' : 'orange'),
                TextColumn::make('transaction_id')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('sender_number')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('price_paid')
                    ->label('Price paid (৳)')
                    ->formatStateUsing(fn ($state): string => '৳ ' . number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn ($state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')
                    ->dateTime('d M Y, g:i A')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Enrolled')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('rejection_reason')
                    ->label('Rejection reason')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(self::statusOptions()),
                SelectFilter::make('payment_method')
                    ->options([
                        'bkash' => 'bKash',
                        'nagad' => 'Nagad',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Enrollment $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve this payment?')
                    ->modalDescription('This will unlock the course for the student immediately.')
                    ->modalSubmitActionLabel('Approve payment')
                    ->action(function (Enrollment $record): void {
                        $record->update([
                            'status' => 'active',
                            'paid_at' => now(),
                            'rejection_reason' => null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Enrollment approved successfully.')
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Enrollment $record): bool => $record->status === 'pending')
                    ->modalHeading('Reject this payment?')
                    ->modalDescription('The course will NOT be unlocked for the student.')
                    ->modalSubmitActionLabel('Reject payment')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Rejection reason')
                            ->helperText('Optional — saved on the record for future reference.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, Enrollment $record): void {
                        $record->update([
                            'status' => 'cancelled',
                            'rejection_reason' => filled($data['reason'] ?? null) ? trim($data['reason']) : null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Enrollment rejected.')
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
