<?php

namespace App\Filament\Resources\Enrollments\Tables;

use App\Filament\Actions\RecordPaymentAction;
use App\Models\Enrollment;
use App\Models\Order;
use App\Services\EnrollmentApprovalService;
use App\Services\PaymentReminderService;
use App\Services\PaymentReviewService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EnrollmentsTable
{
    protected static function paymentColor(?string $status): string
    {
        return match ($status) {
            'paid' => 'success',
            'partially_paid' => 'info',
            'rejected' => 'danger',
            'needs_attention' => 'warning',
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
                    ->getStateUsing(fn (Enrollment $record): string => $record->student_name ?: ($record->user?->name ?? '—'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('course.title')
                    ->label('Course')
                    ->limit(40)
                    ->searchable()
                    ->tooltip(fn ($state) => $state),
                TextColumn::make('amount')
                    ->label('Amount (৳)')
                    ->placeholder('—')
                    ->getStateUsing(fn (Enrollment $record): ?string => $record->unifiedOrder?->total_amount)
                    ->formatStateUsing(fn (?string $state): string => $state === null ? '—' : '৳ '.number_format((float) $state, 2)),
                TextColumn::make('amount_paid')
                    ->label('Paid (৳)')
                    ->placeholder('—')
                    ->getStateUsing(fn (Enrollment $record): ?float => $record->unifiedOrder?->netReceived())
                    ->formatStateUsing(fn (?float $state): string => $state === null ? '—' : '৳ '.number_format($state, 2)),
                TextColumn::make('amount_due')
                    ->label('Due (৳)')
                    ->placeholder('—')
                    ->getStateUsing(fn (Enrollment $record): ?float => $record->unifiedOrder?->dueTotal())
                    ->formatStateUsing(fn (?float $state): string => $state === null ? '—' : '৳ '.number_format($state, 2)),
                TextColumn::make('payment_status')
                    ->label('Payment status')
                    ->badge()
                    ->placeholder('—')
                    ->getStateUsing(fn (Enrollment $record): ?string => $record->unifiedOrder?->reviewStatus())
                    ->formatStateUsing(fn (?string $state): string => $state === null ? '—' : (Enrollment::PAYMENT_STATUSES[$state] ?? $state))
                    ->color(fn (?string $state): string => self::paymentColor($state)),
                TextColumn::make('enrollment_status')
                    ->label('Enrollment status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Enrollment::ENROLLMENT_STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => self::enrollmentColor($state)),
                TextColumn::make('created_at')
                    ->label('Enrolled')
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
                    ->options(Enrollment::PAYMENT_STATUSES),
                SelectFilter::make('enrollment_status')
                    ->label('Enrollment status')
                    ->options(Enrollment::ENROLLMENT_STATUSES),
            ])
            ->recordActions([
                RecordPaymentAction::make()
                    ->visible(fn (Enrollment $record): bool => in_array($record->payment_status, [
                        Enrollment::PAYMENT_STATUS_PENDING,
                        Enrollment::PAYMENT_STATUS_PARTIALLY_PAID,
                        Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION,
                    ], true)),
                Action::make('markPaid')
                    ->label('Approve & mark as paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Enrollment $record): bool => $record->payment_status !== Enrollment::PAYMENT_STATUS_PAID
                        || $record->confirmation_email_sent_at === null)
                    ->action(function (Enrollment $record): void {
                        $result = app(PaymentReviewService::class)->approve($record);

                        if ($result['changed']) {
                            Notification::make()
                                ->success()
                                ->title($result['emailed'] ? 'Payment approved — confirmation email queued.' : 'Payment approved.')
                                ->send();

                            return;
                        }

                        if ($result['emailed']) {
                            Notification::make()
                                ->success()
                                ->title('Confirmation email sent.')
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->info()
                            ->title('Confirmation email not queued.')
                            ->body('The approval email failed and can be retried with this action. No email marker was consumed.')
                            ->send();
                    }),
                Action::make('markCompleted')
                    ->label('Mark as completed')
                    ->icon('heroicon-o-flag')
                    ->color('info')
                    ->visible(fn (Enrollment $record): bool => $record->payment_status === Enrollment::PAYMENT_STATUS_PAID
                        && $record->enrollment_status === 'in_progress')
                    ->action(function (Enrollment $record): void {
                        app(EnrollmentApprovalService::class)->complete($record);

                        Notification::make()->success()->title('Marked as completed.')->send();
                    }),
                Action::make('requestCorrection')
                    ->label('Needs correction')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->visible(fn (Enrollment $record): bool => in_array($record->payment_status, [
                        Enrollment::PAYMENT_STATUS_PENDING,
                        Enrollment::PAYMENT_STATUS_PARTIALLY_PAID,
                        Enrollment::PAYMENT_STATUS_REJECTED,
                    ], true))
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
                    ->action(function (Enrollment $record, array $data): void {
                        $result = app(PaymentReviewService::class)->requestCorrection($record, $data['action_required']);

                        Notification::make()
                            ->success()
                            ->title($result['changed'] ? 'Correction requested.' : 'Payment was not eligible for correction.')
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reject payment')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Enrollment $record): bool => in_array($record->payment_status, [
                        Enrollment::PAYMENT_STATUS_PENDING,
                        Enrollment::PAYMENT_STATUS_PARTIALLY_PAID,
                        Enrollment::PAYMENT_STATUS_NEEDS_ATTENTION,
                    ], true))
                    ->requiresConfirmation()
                    ->modalHeading('Reject this payment?')
                    ->modalDescription('Blocks course access, stores your reason, and emails the student that verification failed.')
                    ->modalSubmitActionLabel('Reject payment')
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Reason (optional)')
                            ->rows(3),
                    ])
                    ->action(function (Enrollment $record, array $data): void {
                        $result = app(PaymentReviewService::class)->reject($record, filled($data['rejection_reason'] ?? null) ? $data['rejection_reason'] : null);

                        Notification::make()
                            ->success()
                            ->title($result['changed'] ? 'Payment rejected.' : 'Payment could not be rejected in its current state.')
                            ->send();
                    }),
                Action::make('sendPaymentReminder')
                    ->label('Send payment reminder')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->visible(fn (Enrollment $record): bool => in_array($record->payment_status, ['pending', 'partially_paid'], true)
                        && (float) $record->amount_due > 0)
                    ->action(function (Enrollment $record): void {
                        $result = app(PaymentReminderService::class)->remind($record);

                        if ($result['queued'] ?? false) {
                            Notification::make()->success()->title('Payment reminder queued.')->send();

                            return;
                        }

                        if ($result['error'] ?? false) {
                            Notification::make()
                                ->danger()
                                ->title('Reminder could not be queued.')
                                ->body('The reminder email failed. The 7-day cooldown was NOT consumed — check the payment_reminder template and try again.')
                                ->send();

                            return;
                        }

                        if ($result['claimed'] ?? false) {
                            Notification::make()
                                ->warning()
                                ->title('Reminder could not be queued.')
                                ->body('Check the payment_reminder template and the recipient email.')
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->info()
                            ->title('Reminder already sent recently.')
                            ->body('A reminder was already sent within the last '.PaymentReminderService::INTERVAL_DAYS.' days.')
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete this enrollment?')
                    ->modalDescription('The enrollment record will be permanently removed.')
                    ->action(function (Enrollment $record, DeleteAction $action): void {
                        DB::transaction(function () use ($record): void {
                            Order::where('legacy_source', Order::SOURCE_ENROLLMENT)
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
                        ->label('Approve & mark as paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $service = app(PaymentReviewService::class);
                            $changed = 0;

                            foreach ($records as $enrollment) {
                                // Only enrollments that actually transition to
                                // paid may be approved here — an already-paid
                                // enrollment is never emailed again.
                                if ($enrollment->payment_status === Enrollment::PAYMENT_STATUS_PAID) {
                                    continue;
                                }

                                if ($service->approve($enrollment)['changed']) {
                                    $changed++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title($changed.' '.($changed === 1 ? 'enrollment' : 'enrollments').' approved.')
                                ->send();
                        }),
                    BulkAction::make('bulkMarkCompleted')
                        ->label('Mark as completed')
                        ->icon('heroicon-o-flag')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $service = app(EnrollmentApprovalService::class);
                            $count = 0;

                            foreach ($records as $enrollment) {
                                if ($service->complete($enrollment)) {
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title($count.' '.($count === 1 ? 'enrollment' : 'enrollments').' marked as completed.')
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete selected enrollments?')
                        ->modalDescription('The selected enrollment records will be permanently removed.')
                        ->before(function (Collection $records): void {
                            Order::where('legacy_source', Order::SOURCE_ENROLLMENT)
                                ->whereIn('legacy_id', $records->pluck('id'))
                                ->delete();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
