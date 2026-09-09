<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class UsersTable
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
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'admin' ? 'warning' : 'gray')
                    ->searchable(),
                ToggleColumn::make('is_admin')
                    ->label('Admin')
                    ->disabled(fn (User $record): bool => $record->is(auth()->user())),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'student' => 'Student',
                    ]),
                TernaryFilter::make('is_admin')
                    ->label('Admin access')
                    ->trueLabel('Admins')
                    ->falseLabel('Non-admins'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => ! $record->is(auth()->user()))
                    ->requiresConfirmation()
                    ->modalHeading('Delete this user?')
                    ->modalDescription('This also removes their enrollments and learning progress. This cannot be undone.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Delete selected users?')
                        ->modalDescription('Their enrollments and learning progress are deleted too. Your own account is never deleted.')
                        ->action(function (Collection $records): void {
                            $deleted = $records
                                ->reject(fn (User $record): bool => $record->is(auth()->user()));

                            $count = $deleted->count();

                            $deleted->each(fn (User $record): bool => $record->delete());

                            Notification::make()
                                ->success()
                                ->title("{$count} user".($count === 1 ? '' : 's').' deleted.')
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
