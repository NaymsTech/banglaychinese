<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('hsk_level')
                    ->formatStateUsing(fn ($state): string => $state ? "HSK {$state}" : '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('is_published')
                    ->label('Published')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Published' : 'Draft')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
                TextColumn::make('published_at')
                    ->dateTime('d M Y, g:i A')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Publishing status')
                    ->trueLabel('Published')
                    ->falseLabel('Draft'),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
