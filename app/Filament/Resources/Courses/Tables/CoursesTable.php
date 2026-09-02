<?php

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CoursesTable
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
                    ->label('Category'),
                TextColumn::make('hsk_level')
                    ->formatStateUsing(fn ($state): string => $state ? "HSK {$state}" : '—'),
                TextColumn::make('price')
                    ->label('Price (৳)')
                    ->formatStateUsing(fn ($state): string => '৳ ' . number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('is_published')
                    ->label('Published')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Published' : 'Draft')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable(),
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
                SelectFilter::make('hsk_level')
                    ->label('HSK level')
                    ->options([
                        1 => 'HSK 1',
                        2 => 'HSK 2',
                        3 => 'HSK 3',
                        4 => 'HSK 4',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
