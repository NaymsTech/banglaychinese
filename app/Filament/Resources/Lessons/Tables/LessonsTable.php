<?php

namespace App\Filament\Resources\Lessons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LessonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('module.title')
                    ->label('Module')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('video_url')
                    ->label('Video')
                    ->url(fn ($state): ?string => filled($state) ? $state : null)
                    ->openUrlInNewTab()
                    ->placeholder('—'),
                TextColumn::make('order')
                    ->alignCenter(),
                IconColumn::make('is_free_preview')
                    ->label('Free')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-eye' : 'heroicon-o-lock-closed')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order');
    }
}
