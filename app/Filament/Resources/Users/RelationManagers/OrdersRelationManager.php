<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only list of one customer's canonical orders. Rows open the existing
 * unified Order detail page (ViewOrder), so no Order UI is duplicated here.
 */
class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student_name')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['items', 'payments']))
            ->columns([
                TextColumn::make('id')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('purchased')
                    ->label('Purchased')
                    ->getStateUsing(fn (Order $record): string => $record->items
                        ->map(fn ($item): string => (string) $item->title)
                        ->filter()
                        ->implode(', ') ?: '—')
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Type')
                    ->getStateUsing(fn (Order $record): string => OrderResource::purchaseType($record)),
                TextColumn::make('total_amount')
                    ->label('Total (৳)')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => '৳ '.number_format((float) $state, 2)),
                TextColumn::make('paid')
                    ->label('Paid (৳)')
                    ->getStateUsing(fn (Order $record): float => $record->paidTotal())
                    ->formatStateUsing(fn (float $state): string => '৳ '.number_format($state, 2)),
                TextColumn::make('refunded')
                    ->label('Refunded (৳)')
                    ->getStateUsing(fn (Order $record): float => $record->refundedTotal())
                    ->formatStateUsing(fn (float $state): string => '৳ '.number_format($state, 2)),
                TextColumn::make('due')
                    ->label('Due (৳)')
                    ->getStateUsing(fn (Order $record): float => $record->dueTotal())
                    ->formatStateUsing(fn (float $state): string => '৳ '.number_format($state, 2))
                    ->color(fn (Order $record): string => $record->dueTotal() > 0.009 ? 'warning' : 'success'),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->getStateUsing(fn (Order $record): string => $record->reviewStatus())
                    ->formatStateUsing(fn (string $state): string => (Payment::STATUSES[$state] ?? ucwords(str_replace('_', ' ', $state))))
                    ->color(fn (string $state): string => OrderResource::paymentColor($state)),
                TextColumn::make('order_status')
                    ->label('Order status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => (Order::STATUSES[$state] ?? $state))
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_COMPLETED => 'success',
                        Order::STATUS_IN_PROGRESS => 'info',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('No orders yet')
            ->defaultSort('created_at', 'desc');
    }
}
