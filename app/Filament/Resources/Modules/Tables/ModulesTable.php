<?php

namespace App\Filament\Resources\Modules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ModulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('lessons'))
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('lessons_count')
                    ->label('Lessons')
                    ->alignCenter(),
                TextColumn::make('order')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
