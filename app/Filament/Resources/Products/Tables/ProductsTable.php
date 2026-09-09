<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('price')
                    ->label('Price (৳)')
                    ->formatStateUsing(fn (string $state): string => '৳ '.number_format((float) $state, 2))
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('Published')
                    ->onColor('success')
                    ->offColor('gray')
                    ->tooltip('Quick toggle between hidden and published'),
                TextColumn::make('digital_orders_count')
                    ->label('Orders')
                    ->counts('digitalOrders')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(fn (): array => Product::query()
                        ->whereNotNull('category')
                        ->pluck('category', 'category')
                        ->all()),
                SelectFilter::make('is_published')
                    ->label('Published')
                    ->options([
                        '1' => 'Published',
                        '0' => 'Hidden',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('togglePublish')
                        ->label('Toggle publish')
                        ->icon('heroicon-o-eye')
                        ->requiresConfirmation()
                        ->modalHeading('Toggle publish on selected products?')
                        ->modalDescription('Published products become hidden and hidden products become published.')
                        ->modalSubmitActionLabel('Toggle publish')
                        ->action(function (Collection $records): void {
                            $records->each(function (Product $product): void {
                                $product->update(['is_published' => ! $product->is_published]);
                            });
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
