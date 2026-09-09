<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Services\OrderCompletionService;
use App\Services\PaymentReminderService;
use App\Services\PaymentReviewService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $anchor = OrderResource::resolveAnchor($this->getRecord());

        $actions = [];

        if ($anchor instanceof Enrollment || $anchor instanceof DigitalOrder) {
            $actions[] = $this->approveAction($anchor);
            $actions[] = $this->requestCorrectionAction($anchor);
            $actions[] = $this->rejectAction($anchor);
        }

        $actions[] = $this->recordPaymentAction($anchor);

        $record = $this->getRecord();

        if ($anchor instanceof Enrollment && $record->order_status === Order::STATUS_IN_PROGRESS) {
            $actions[] = $this->markCompletedAction($record);
        } elseif ($anchor instanceof ServiceOrder && ! in_array($anchor->enrollment_status, ['completed', 'cancelled'], true)) {
            $actions[] = $this->markCompletedAction($record);
        }

        if ($anchor instanceof Enrollment
            && $record->dueTotal() > 0
            && in_array($record->reviewStatus(), ['pending', 'partially_paid'], true)) {
            $actions[] = $this->sendReminderAction($anchor);
        }

        return $actions;
    }

    protected function markCompletedAction(Order $record): Action
    {
        return Action::make('markCompleted')
            ->label('Mark Completed')
            ->color('success')
            ->requiresConfirmation()
            ->action(function () use ($record): void {
                $result = app(OrderCompletionService::class)->complete($record);

                if (! $result['changed']) {
                    Notification::make()->info()->title($result['message'])->send();

                    return;
                }

                Notification::make()->success()->title($result['message'])->send();
                $this->redirect(self::getUrl(['record' => $record]), navigate: true);
            });
    }

    protected function sendReminderAction(Enrollment $enrollment): Action
    {
        return Action::make('sendReminder')
            ->label('Send Payment Reminder')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function () use ($enrollment): void {
                $result = app(PaymentReminderService::class)->remind($enrollment);

                if ($result['queued'] ?? false) {
                    Notification::make()->success()->title('Payment reminder queued.')->send();
                } elseif ($result['error'] ?? false) {
                    Notification::make()
                        ->danger()
                        ->title('Reminder could not be queued.')
                        ->body('The reminder email failed. The cooldown was not consumed — check the payment_reminder template and try again.')
                        ->send();
                } elseif ($result['claimed'] ?? false) {
                    Notification::make()
                        ->warning()
                        ->title('Reminder could not be queued.')
                        ->body('Check the payment_reminder template and the recipient email.')
                        ->send();
                } else {
                    Notification::make()
                        ->info()
                        ->title('Reminder already sent recently.')
                        ->body('A reminder was already sent within the cooldown period.')
                        ->send();
                }
            });
    }

    protected function approveAction(Enrollment|DigitalOrder $anchor): Action
    {
        return Action::make('approve')
            ->label('Approve payment')
            ->color('success')
            ->requiresConfirmation()
            ->action(function () use ($anchor): void {
                $result = app(PaymentReviewService::class)->approve($anchor);

                $this->notifyResult('Payment approved.', $result['changed'], $result['emailed']);
                $this->redirect(self::getUrl(['record' => $this->getRecord()]), navigate: true);
            });
    }

    protected function requestCorrectionAction(Enrollment|DigitalOrder $anchor): Action
    {
        return Action::make('requestCorrection')
            ->label('Request correction')
            ->color('warning')
            ->schema([
                Textarea::make('action_required')->label('What must the customer correct?')->required()->rows(3),
            ])
            ->action(function (array $data) use ($anchor): void {
                $result = app(PaymentReviewService::class)->requestCorrection($anchor, $data['action_required']);

                $this->notifyResult('Correction requested.', $result['changed'], $result['emailed']);
                $this->redirect(self::getUrl(['record' => $this->getRecord()]), navigate: true);
            });
    }

    protected function rejectAction(Enrollment|DigitalOrder $anchor): Action
    {
        return Action::make('reject')
            ->label('Reject payment')
            ->color('danger')
            ->schema([
                Textarea::make('rejection_reason')->label('Reason (optional)')->rows(3),
            ])
            ->action(function (array $data) use ($anchor): void {
                $result = app(PaymentReviewService::class)->reject($anchor, filled($data['rejection_reason'] ?? null) ? $data['rejection_reason'] : null);

                $this->notifyResult('Payment rejected.', $result['changed'], $result['emailed']);
                $this->redirect(self::getUrl(['record' => $this->getRecord()]), navigate: true);
            });
    }

    protected function recordPaymentAction(Enrollment|DigitalOrder|ServiceOrder|null $anchor): Action
    {
        $record = $this->getRecord();

        return Action::make('recordPayment')
            ->label('Record payment')
            ->color('info')
            ->schema([
                TextInput::make('amount')->label('Payment amount (৳)')->numeric()->required()->minValue(0.01)->prefix('৳')
                    ->helperText(sprintf('Current due: ৳%s', number_format($record->dueTotal(), 2))),
                Select::make('method')->label('Payment method')->options(Payment::METHODS)->placeholder('— Select —'),
                TextInput::make('trx_reference')->label('Transaction reference')->maxLength(255),
                TextInput::make('sender_number')->label('Sender number')->maxLength(30),
                Textarea::make('review_note')->label('Note')->rows(2),
            ])
            ->action(function (array $data) use ($anchor, $record): void {
                if ($anchor === null) {
                    Notification::make()->danger()->title('No fulfillment record for this order.')->send();

                    return;
                }

                try {
                    $result = app(PaymentReviewService::class)->recordPayment($anchor, $data);

                    Notification::make()
                        ->success()
                        ->title('Payment recorded.')
                        ->body(sprintf('Paid: ৳%s · Due: ৳%s', number_format($result['paid_total'], 2), number_format($result['due'], 2)))
                        ->send();
                } catch (\InvalidArgumentException $exception) {
                    Notification::make()->danger()->title($exception->getMessage())->send();

                    return;
                } catch (\Throwable $exception) {
                    Log::warning('Record payment failed.', ['order_id' => $record->id, 'error' => $exception->getMessage()]);
                    Notification::make()->danger()->title('Payment could not be recorded.')->send();

                    return;
                }

                $this->redirect(self::getUrl(['record' => $this->getRecord()]), navigate: true);
            });
    }

    protected function notifyResult(string $title, bool $changed, bool $emailed): void
    {
        Notification::make()
            ->success()
            ->title($title)
            ->body($changed ? ($emailed ? 'State updated and email queued.' : 'State updated.') : ($emailed ? 'Already in this state; email was sent.' : 'No change was needed.'))
            ->send();
    }
}
