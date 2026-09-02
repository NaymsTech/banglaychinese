<?php

namespace App\Filament\Resources\MentorshipBookings\Tables;

use App\Models\MentorshipBooking;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MentorshipBookingsTable
{
    public static function statusColors(): array
    {
        return [
            'pending' => 'warning',
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('mentor.name')
                    ->label('Mentor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->dateTime('d M Y, g:i A')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => MentorshipBooking::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn ($state): string => self::statusColors()[$state] ?? 'gray'),
                TextColumn::make('meeting_link')
                    ->label('Meeting link')
                    ->url(fn ($state): ?string => filled($state) ? $state : null)
                    ->openUrlInNewTab()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Booked')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(MentorshipBooking::STATUS_OPTIONS),
                SelectFilter::make('mentor_id')
                    ->relationship('mentor', 'name')
                    ->label('Mentor')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('confirm')
                    ->label('Confirm booking')
                    ->icon('heroicon-o-video-camera')
                    ->color('success')
                    ->visible(fn (MentorshipBooking $record): bool => $record->status === MentorshipBooking::STATUS_PENDING)
                    ->modalHeading('Confirm booking')
                    ->modalDescription('Provide the meeting link that will be shared with the student.')
                    ->modalSubmitActionLabel('Confirm booking')
                    ->schema([
                        TextInput::make('meeting_link')
                            ->label('Meeting link')
                            ->url()
                            ->required()
                            ->placeholder('https://meet.google.com/...'),
                    ])
                    ->action(function (array $data, MentorshipBooking $record): void {
                        $record->update([
                            'status' => MentorshipBooking::STATUS_CONFIRMED,
                            'meeting_link' => $data['meeting_link'],
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Booking confirmed.')
                            ->send();
                    }),
                Action::make('cancel')
                    ->label('Cancel booking')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (MentorshipBooking $record): bool => in_array($record->status, [
                        MentorshipBooking::STATUS_PENDING,
                        MentorshipBooking::STATUS_CONFIRMED,
                    ], true))
                    ->requiresConfirmation()
                    ->modalHeading('Cancel this booking?')
                    ->modalSubmitActionLabel('Cancel booking')
                    ->action(function (MentorshipBooking $record): void {
                        $record->update(['status' => MentorshipBooking::STATUS_CANCELLED]);

                        Notification::make()
                            ->success()
                            ->title('Booking cancelled.')
                            ->send();
                    }),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }
}
