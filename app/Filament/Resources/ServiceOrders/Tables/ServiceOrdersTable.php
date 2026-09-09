<?php

namespace App\Filament\Resources\ServiceOrders\Tables;

use App\Filament\Actions\RecordPaymentAction;
use App\Models\Order;
use App\Models\ServiceOrder;
use App\Services\OrderCompletionService;
use App\Services\OrderMaterializer;
use App\Services\PaymentReviewService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ServiceOrdersTable
{
    protected static function paymentColor(string $status): string
    {
        return match ($status) {
            'paid' => 'success',
            'partially_paid' => 'info',
            'refunded' => 'gray',
            default => 'warning',
        };
    }

    protected static function enrollmentColor(string $status): string
    {
        return match ($status) {
            'completed' => 'success',
            'in_progress' => 'info',
            'cancelled' => 'danger',
            default => 'warning',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('service.name')
                    ->label('Package')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount (৳)')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('amount_paid')
                    ->label('Paid (৳)')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('amount_due')
                    ->label('Due (৳)')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Payment status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ServiceOrder::PAYMENT_STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => self::paymentColor($state)),
                TextColumn::make('enrollment_status')
                    ->label('Order status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ServiceOrder::ENROLLMENT_STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => self::enrollmentColor($state)),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('student_phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('student_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Payment status')
                    ->options(ServiceOrder::PAYMENT_STATUSES),
                SelectFilter::make('enrollment_status')
                    ->label('Order status')
                    ->options(ServiceOrder::ENROLLMENT_STATUSES),
            ])
            ->recordActions([
                RecordPaymentAction::make()
                    ->visible(fn (ServiceOrder $record): bool => in_array($record->payment_status, ['pending', 'partially_paid'], true)),
                Action::make('markPaid')
                    ->label('Mark as paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ServiceOrder $record): bool => ! in_array($record->payment_status, ['paid', 'refunded'], true))
                    ->action(function (ServiceOrder $record): void {
                        $due = self::outstandingDue($record);

                        if ($due <= 0.009) {
                            Notification::make()
                                ->info()
                                ->title('Nothing left to settle.')
                                ->send();

                            return;
                        }

                        try {
                            app(PaymentReviewService::class)->recordPayment($record, [
                                'amount' => round($due, 2),
                                'method' => $record->payment_method,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Marked as paid — order moved to In Progress.')
                                ->send();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->danger()
                                ->title('Order could not be marked as paid.')
                                ->body($exception->getMessage())
                                ->send();
                        }
                    }),
                Action::make('markCompleted')
                    ->label('Mark as completed')
                    ->icon('heroicon-o-flag')
                    ->color('info')
                    ->visible(fn (ServiceOrder $record): bool => ! in_array($record->enrollment_status, ['completed', 'cancelled'], true))
                    ->action(function (ServiceOrder $record): void {
                        $result = self::completeOrder($record);

                        Notification::make()
                            ->success()
                            ->title($result ? 'Marked as completed.' : 'This order is already completed or cancelled.')
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete this order?')
                    ->modalDescription('The order record and its canonical Order/Payment ledger will be permanently removed.')
                    ->action(function (ServiceOrder $record, DeleteAction $action): void {
                        DB::transaction(function () use ($record): void {
                            Order::where('legacy_source', Order::SOURCE_SERVICE_ORDER)
                                ->where('legacy_id', $record->getKey())
                                ->delete();

                            $record->delete();
                        });

                        $action->success();
                    }),
            ])
            ->selectable()
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkMarkPaid')
                        ->label('Mark as paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $changed = 0;

                            foreach ($records as $serviceOrder) {
                                if (in_array($serviceOrder->payment_status, ['paid', 'refunded'], true)) {
                                    continue;
                                }

                                $due = self::outstandingDue($serviceOrder);

                                if ($due <= 0.009) {
                                    continue;
                                }

                                try {
                                    app(PaymentReviewService::class)->recordPayment($serviceOrder, [
                                        'amount' => round($due, 2),
                                        'method' => $serviceOrder->payment_method,
                                    ]);
                                    $changed++;
                                } catch (Throwable) {
                                    // A single order failing (e.g. a data
                                    // anomaly) must not abort the bulk run.
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title($changed.' '.($changed === 1 ? 'order' : 'orders').' marked as paid.')
                                ->send();
                        }),
                    BulkAction::make('bulkMarkCompleted')
                        ->label('Mark as completed')
                        ->icon('heroicon-o-flag')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $count = 0;

                            foreach ($records as $serviceOrder) {
                                if (self::completeOrder($serviceOrder)) {
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title($count.' '.($count === 1 ? 'order' : 'orders').' marked as completed.')
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete selected orders?')
                        ->modalDescription('The selected order records and their canonical Order/Payment ledgers will be permanently removed.')
                        ->before(function (Collection $records): void {
                            Order::where('legacy_source', Order::SOURCE_SERVICE_ORDER)
                                ->whereIn('legacy_id', $records->pluck('id'))
                                ->delete();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Settle what the canonical ledger still records as due. When a row has
     * no canonical order yet (e.g. rows recorded before materialization was
     * added), it is materialized first so the balance always comes from the
     * canonical Payment ledger — never from hand-maintained legacy columns.
     */
    protected static function outstandingDue(ServiceOrder $serviceOrder): float
    {
        $order = Order::where('legacy_source', Order::SOURCE_SERVICE_ORDER)
            ->where('legacy_id', $serviceOrder->getKey())
            ->first();

        if ($order === null) {
            $materialized = app(OrderMaterializer::class)->materialize($serviceOrder);
            $order = $materialized['order'];
        }

        return $order?->dueTotal() ?? 0.0;
    }

    /**
     * Mirror the canonical Order before completing so the Orders resource and
     * OrderCompletionService stay coherent with the legacy row.
     */
    protected static function completeOrder(ServiceOrder $serviceOrder): bool
    {
        if (in_array($serviceOrder->enrollment_status, ['completed', 'cancelled'], true)) {
            return false;
        }

        $materialized = app(OrderMaterializer::class)->materialize($serviceOrder);

        if ($materialized['order'] === null) {
            return false;
        }

        return app(OrderCompletionService::class)->complete($materialized['order'])['changed'];
    }
}
