<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\ContactMessage;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('topic')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('message')
                    ->limit(80)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_read')
                    ->label('Read')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d M Y, g:i A')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_read')
                    ->label('Read status')
                    ->trueLabel('Read')
                    ->falseLabel('Unread'),
            ])
            ->selectable()
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete this message?')
                    ->modalDescription('The message will be permanently removed.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markAsRead')
                        ->label('Mark as read')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records): void {
                            $count = $records->count();

                            ContactMessage::whereIn('id', $records->pluck('id'))
                                ->update(['is_read' => true]);

                            Notification::make()
                                ->success()
                                ->title("{$count} message" . ($count === 1 ? '' : 's') . ' marked as read.')
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
