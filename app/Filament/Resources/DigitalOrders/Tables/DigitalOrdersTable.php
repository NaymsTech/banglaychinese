<?php

namespace App\Filament\Resources\DigitalOrders\Tables;

use App\Filament\Actions\RecordPaymentAction;
use App\Models\DigitalOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentReviewService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DigitalOrdersTable
{
    protected static function statusColor(?string $status): string
    {
        return match ($status) {
            DigitalOrder::STATUS_APPROVED => 'success',
            DigitalOrder::STATUS_REJECTED => 'danger',
            DigitalOrder::STATUS_NEEDS_ATTENTION => 'warning',
            default => 'warning',
        };
    }

    /**
     * Canonical review state expressed with the legacy digital-order status
     * vocabulary. A needs-attention claim still displays as "pending" because
     * the legacy status column never held that value.
     */
    protected static function canonicalDigitalStatus(?Order $order): ?string
    {
        if ($order === null) {
            return null;
        }

        return match ($order->reviewStatus()) {
            'paid' => DigitalOrder::STATUS_APPROVED,
            'rejected' => DigitalOrder::STATUS_REJECTED,
            default => DigitalOrder::STATUS_PENDING,
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
                TextColumn::make('product.title')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount (৳)')
                    ->placeholder('—')
                    ->getStateUsing(fn (DigitalOrder $record): ?string => $record->unifiedOrder?->total_amount)
                    ->formatStateUsing(fn (?string $state): string => $state === null ? '—' : '৳ '.number_format((float) $state, 2)),
                TextColumn::make('trx_id')
                    ->label('Trx ID')
                    ->getStateUsing(fn (DigitalOrder $record): ?string => $record->unifiedOrder?->payments
                        ->first(fn (Payment $payment): bool => filled($payment->trx_reference))?->trx_reference)
                    // Transaction search is canonical: only active Payment
                    // references (pending / needs_attention / paid) match.
                    // Rejected and refunded rows are stale by definition, and
                    // the legacy digital_orders.trx_id column is never searched.
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->orWhereHas('unifiedOrder.payments', function (Builder $payments) use ($search): void {
                            $payments
                                ->whereIn('status', [
                                    Payment::STATUS_PENDING,
                                    Payment::STATUS_NEEDS_ATTENTION,
                                    Payment::STATUS_PAID,
                                ])
                                ->where('trx_reference', 'like', '%'.$search.'%');
                        });
                    })
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->placeholder('—')
                    ->getStateUsing(fn (DigitalOrder $record): ?string => self::canonicalDigitalStatus($record->unifiedOrder))
                    ->formatStateUsing(fn (?string $state): string => $state === null ? '—' : (DigitalOrder::STATUSES[$state] ?? $state))
                    ->color(fn (?string $state): string => self::statusColor($state)),
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(DigitalOrder::STATUSES),
            ])
            ->recordActions([
                RecordPaymentAction::make()
                    ->visible(fn (DigitalOrder $record): bool => $record->status === DigitalOrder::STATUS_PENDING),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (DigitalOrder $record): bool => $record->status !== DigitalOrder::STATUS_APPROVED
                        || $record->approval_email_sent_at === null)
                    ->action(function (DigitalOrder $record): void {
                        $result = app(PaymentReviewService::class)->approve($record);

                        $notification = Notification::make()
                            ->title($result['changed'] ? 'Order approved.' : 'Order was already approved.');

                        if ($result['changed'] && ! $result['emailed']) {
                            $notification
                                ->warning()
                                ->body('The approval email could not be queued. Re-run Approve to retry it.');
                        } else {
                            $notification->success();
                        }

                        $notification->send();
                    }),
                Action::make('requestCorrection')
                    ->label('Needs correction')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->visible(fn (DigitalOrder $record): bool => $record->status === DigitalOrder::STATUS_PENDING
                        && $record->attention_reason === null)
                    ->requiresConfirmation()
                    ->modalHeading('Request corrected payment info?')
                    ->modalDescription('Flags the payment as needing corrected information and emails the student what to do.')
                    ->modalSubmitActionLabel('Request correction')
                    ->schema([
                        Textarea::make('action_required')
                            ->label('What must the student correct?')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (DigitalOrder $record, array $data): void {
                        $result = app(PaymentReviewService::class)->requestCorrection($record, $data['action_required']);

                        Notification::make()
                            ->success()
                            ->title($result['changed'] ? 'Correction requested.' : 'Order was not eligible for correction.')
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reject payment')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (DigitalOrder $record): bool => $record->status !== DigitalOrder::STATUS_REJECTED)
                    ->requiresConfirmation()
                    ->modalHeading('Reject this payment?')
                    ->modalDescription('Rejects the payment, blocks the download, stores your reason, and emails the student. A later legitimate approval will send the approval email again.')
                    ->modalSubmitActionLabel('Reject payment')
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Reason (optional)')
                            ->rows(3),
                    ])
                    ->action(function (DigitalOrder $record, array $data): void {
                        $result = app(PaymentReviewService::class)->reject($record, filled($data['rejection_reason'] ?? null) ? $data['rejection_reason'] : null);

                        Notification::make()
                            ->success()
                            ->title($result['changed'] ? 'Payment rejected.' : 'Order could not be rejected in its current state.')
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make()
                    ->action(function (DigitalOrder $record, DeleteAction $action): void {
                        DB::transaction(function () use ($record): void {
                            Order::where('legacy_source', Order::SOURCE_DIGITAL_ORDER)
                                ->where('legacy_id', $record->getKey())
                                ->delete();

                            $record->delete();
                        });

                        $action->success();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records): void {
                            Order::where('legacy_source', Order::SOURCE_DIGITAL_ORDER)
                                ->whereIn('legacy_id', $records->pluck('id'))
                                ->delete();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
