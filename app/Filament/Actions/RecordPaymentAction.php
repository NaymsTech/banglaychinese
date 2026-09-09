<?php

namespace App\Filament\Actions;

use App\Models\Payment;
use App\Services\PaymentReviewService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * Admin "Record payment" action shared by the sale list resources.
 *
 * One small UI action instead of three copies: it always routes through the
 * canonical PaymentReviewService::recordPayment, which appends a NEW canonical
 * Payment row (never overwrites history), derives the balance, and mirrors the
 * totals onto the legacy sale row in the same transaction. Digital products
 * only accept the full outstanding amount, enforced by the service.
 */
class RecordPaymentAction
{
    public static function make(): Action
    {
        return Action::make('recordPayment')
            ->label('Record payment')
            ->icon('heroicon-o-banknotes')
            ->color('info')
            ->schema([
                TextInput::make('amount')
                    ->label('Payment amount (৳)')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->prefix('৳')
                    ->helperText('Money actually received. The remaining balance is derived from the canonical payments — never typed.'),
                Select::make('method')
                    ->label('Payment method')
                    ->options(Payment::METHODS)
                    ->placeholder('— Select —'),
                TextInput::make('trx_reference')
                    ->label('Transaction reference')
                    ->maxLength(255),
                TextInput::make('sender_number')
                    ->label('Sender number')
                    ->maxLength(30),
                Textarea::make('review_note')
                    ->label('Note')
                    ->rows(2),
            ])
            ->action(function (Model $record, array $data): void {
                try {
                    $result = app(PaymentReviewService::class)->recordPayment($record, $data);

                    Notification::make()
                        ->success()
                        ->title('Payment recorded.')
                        ->body(sprintf('Paid: ৳%s · Due: ৳%s', number_format($result['paid_total'], 2), number_format($result['due'], 2)))
                        ->send();
                } catch (InvalidArgumentException $exception) {
                    Notification::make()->danger()->title($exception->getMessage())->send();
                } catch (Throwable $exception) {
                    Log::warning('Record payment failed.', ['sale_type' => $record::class, 'sale_id' => $record->getKey(), 'error' => $exception->getMessage()]);
                    Notification::make()->danger()->title('Payment could not be recorded.')->send();
                }
            });
    }
}
