<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Users\UserResource;
use App\Models\Course;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceOrder;
use BackedEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 33;

    protected static ?string $recordTitleAttribute = 'student_name';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Admissions & CRM';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'items', 'payments'])
            ->latest('created_at');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_name')
                    ->label('Customer')
                    ->getStateUsing(fn (Order $record): string => $record->student_name ?: ($record->user?->name ?? '—'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    // Opens the customer's order history when an account is linked.
                    ->url(fn (Order $record): ?string => $record->user_id !== null
                        ? UserResource::getUrl('view', ['record' => $record->user_id])
                        : null),
                TextColumn::make('student_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('student_phone')
                    ->label('WhatsApp')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reference_search')
                    ->label('Reference')
                    ->getStateUsing(fn (Order $record): ?string => $record->payments
                        ->first(fn (Payment $payment): bool => filled($payment->trx_reference))?->trx_reference)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true)
                    // Transaction search uses canonical Payment data.
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->orWhereHas('payments', function (Builder $payments) use ($search): void {
                            $payments->where('trx_reference', 'like', '%'.$search.'%');
                        });
                    }),
                TextColumn::make('purchased')
                    ->label('Purchased')
                    ->getStateUsing(fn (Order $record): string => $record->items->pluck('title')->implode(', '))
                    ->limit(45)
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Type')
                    ->getStateUsing(fn (Order $record): string => self::purchaseType($record)),
                TextColumn::make('total_amount')
                    ->label('Total (৳)')
                    ->formatStateUsing(fn (string $state): string => '৳ '.number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('paid')
                    ->label('Paid (৳)')
                    ->getStateUsing(fn (Order $record): float => $record->paidTotal())
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
                    ->color(fn (string $state): string => self::paymentColor($state)),
                TextColumn::make('order_status')
                    ->label('Order')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::STATUSES[$state] ?? $state)
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
            ->filters([
                SelectFilter::make('order_status')
                    ->label('Order status')
                    ->options(Order::STATUSES),
                SelectFilter::make('purchase_type')
                    ->label('Purchase type')
                    ->options([
                        'course' => 'Course',
                        'product' => 'Digital Product',
                        'service' => 'Service',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => self::filterPurchaseType($query, $data)),
                SelectFilter::make('user_id')
                    ->label('Customer')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('payment_state')
                    ->label('Payment status')
                    ->options([
                        'paid' => 'Paid',
                        'partially_paid' => 'Partially Paid',
                        'unpaid' => 'Unpaid / Pending',
                        'rejected' => 'Rejected',
                        'needs_attention' => 'Needs Attention',
                        'refunded' => 'Refunded',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => self::filterPaymentState($query, $data)),
                Filter::make('outstanding')
                    ->label('Has amount due')
                    ->query(fn (Builder $query): Builder => $query->whereRaw(self::netSubquery().' < orders.total_amount - 0.001')),
                Filter::make('needs_attention')
                    ->label('Needs attention')
                    ->query(fn (Builder $query): Builder => $query->whereHas('payments', fn (Builder $payments): Builder => $payments->where('status', Payment::STATUS_NEEDS_ATTENTION))),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer')->columns(2)->schema([
                TextEntry::make('user.name')->label('User')
                    ->placeholder('Guest / linked user')
                    ->url(fn (Order $record): ?string => $record->user_id !== null
                        ? UserResource::getUrl('view', ['record' => $record->user_id])
                        : null),
                TextEntry::make('student_name')->label('Customer name')
                    ->url(fn (Order $record): ?string => $record->user_id !== null
                        ? UserResource::getUrl('view', ['record' => $record->user_id])
                        : null),
                TextEntry::make('student_email')->label('Email'),
                TextEntry::make('student_phone')->label('WhatsApp / phone'),
            ]),
            Section::make('Purchase')->columns(3)->schema([
                TextEntry::make('purchased')->label('Purchased item(s)')
                    ->state(fn (Order $record): string => $record->items->map(
                        fn ($item): string => $item->title.' × '.$item->quantity
                    )->implode("\n")),
                TextEntry::make('type')->label('Purchase type')
                    ->state(fn (Order $record): string => self::purchaseType($record)),
                TextEntry::make('total_amount')->label('Order total')
                    ->state(fn (Order $record): string => '৳ '.number_format((float) $record->total_amount, 2)),
            ]),
            Section::make('Payment summary')->columns(4)->schema([
                TextEntry::make('total_amount')->label('Total')
                    ->state(fn (Order $record): string => '৳ '.number_format((float) $record->total_amount, 2)),
                TextEntry::make('paid')->label('Paid')
                    ->state(fn (Order $record): string => '৳ '.number_format($record->paidTotal(), 2)),
                TextEntry::make('refunded')->label('Refunded')
                    ->state(fn (Order $record): string => '৳ '.number_format($record->refundedTotal(), 2)),
                TextEntry::make('due')->label('**Amount due**')
                    ->state(fn (Order $record): string => '৳ '.number_format($record->dueTotal(), 2))
                    ->color(fn (Order $record): string => $record->dueTotal() > 0.009 ? 'warning' : 'success'),
            ]),
            Section::make('Order status')->columns(3)->schema([
                TextEntry::make('order_status')->label('Order status')
                    ->badge(),
                TextEntry::make('created_at')->label('Created')->dateTime(),
                TextEntry::make('updated_at')->label('Updated')->dateTime(),
            ]),
            Section::make('Payment history')->schema([
                RepeatableEntry::make('payments')->label('Payments')->schema([
                    TextEntry::make('amount')->label('Amount')->state(fn (Payment $payment): string => '৳ '.number_format((float) $payment->amount, 2)),
                    TextEntry::make('method')->label('Method'),
                    TextEntry::make('trx_reference')->label('Reference'),
                    TextEntry::make('sender_number')->label('Sender'),
                    TextEntry::make('status')->label('Status'),
                    TextEntry::make('paid_at')->label('Paid at')->dateTime()->placeholder('—'),
                    TextEntry::make('rejected_at')->label('Rejected at')->dateTime()->placeholder('—'),
                    TextEntry::make('refunded_at')->label('Refunded at')->dateTime()->placeholder('—'),
                    TextEntry::make('review_note')->label('Review note')->placeholder('—'),
                    TextEntry::make('created_at')->label('Recorded')->dateTime(),
                ])->columns(4),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }

    /**
     * Resolve the legacy fulfillment anchor for a canonical Order so review
     * actions can reuse PaymentReviewService.
     */
    public static function resolveAnchor(Order $order): ?Model
    {
        return match ($order->legacy_source) {
            Order::SOURCE_ENROLLMENT => Enrollment::find($order->legacy_id),
            Order::SOURCE_DIGITAL_ORDER => DigitalOrder::find($order->legacy_id),
            Order::SOURCE_SERVICE_ORDER => ServiceOrder::find($order->legacy_id),
            default => null,
        };
    }

    public static function purchaseType(Order $record): string
    {
        $type = $record->items->first()?->purchasable_type;

        return match ($type) {
            Course::class => 'Course',
            Product::class => 'Digital Product',
            Service::class => 'Service',
            default => $type ? class_basename($type) : '—',
        };
    }

    public static function paymentColor(string $state): string
    {
        return match ($state) {
            'paid' => 'success',
            'partially_paid' => 'info',
            'rejected' => 'danger',
            'needs_attention' => 'warning',
            'refunded' => 'gray',
            default => 'warning',
        };
    }

    protected static function netSubquery(): string
    {
        return '(COALESCE((SELECT SUM(amount) FROM payments WHERE order_id = orders.id AND status = \'paid\'), 0)'
            .' - COALESCE((SELECT SUM(amount) FROM payments WHERE order_id = orders.id AND status = \'refunded\'), 0))';
    }

    protected static function filterPurchaseType(Builder $query, array $data): Builder
    {
        $class = match ($data['value'] ?? null) {
            'course' => Course::class,
            'product' => Product::class,
            'service' => Service::class,
            default => null,
        };

        if ($class === null) {
            return $query;
        }

        return $query->whereHas('items', fn (Builder $items): Builder => $items->where('purchasable_type', $class));
    }

    protected static function filterPaymentState(Builder $query, array $data): Builder
    {
        $net = self::netSubquery();
        $total = 'orders.total_amount';

        return match ($data['value'] ?? null) {
            'paid' => $query->whereRaw($net.' >= '.$total.' - 0.001'),
            'partially_paid' => $query->whereRaw($net.' > 0 AND '.$net.' < '.$total),
            'unpaid' => $query->whereRaw($net.' <= 0 AND '.$total.' > 0'),
            'rejected' => $query->whereDoesntHave('payments', fn (Builder $p): Builder => $p->where('status', Payment::STATUS_PAID))
                ->whereHas('payments', fn (Builder $p): Builder => $p->where('status', Payment::STATUS_REJECTED)),
            'needs_attention' => $query->whereHas('payments', fn (Builder $p): Builder => $p->where('status', Payment::STATUS_NEEDS_ATTENTION)),
            'refunded' => $query->whereDoesntHave('payments', fn (Builder $p): Builder => $p->where('status', Payment::STATUS_PAID))
                ->whereHas('payments', fn (Builder $p): Builder => $p->where('status', Payment::STATUS_REFUNDED)),
            default => $query,
        };
    }
}
