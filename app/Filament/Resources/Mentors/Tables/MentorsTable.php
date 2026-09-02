<?php

namespace App\Filament\Resources\Mentors\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MentorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('expertise')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('hourly_rate')
                    ->label('Rate (৳)')
                    ->formatStateUsing(fn ($state): string => '৳ ' . number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('is_available')
                    ->label('Available')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Available' : 'Unavailable')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->trueLabel('Available')
                    ->falseLabel('Unavailable'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->defaultSort('name');
    }
}
