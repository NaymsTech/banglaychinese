<?php

namespace App\Filament\Resources\ServiceOrders\Schemas;

use App\Models\ServiceOrder;
use App\Support\BuyerSnapshotAutofill;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ServiceOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Student Details')
                ->description('Who bought the package — link their account when they have one.')
                ->columns(2)
                ->schema([
                    TextInput::make('student_name')
                        ->label('Student name')
                        ->required(fn (Get $get): bool => blank($get('user_id')))
                        ->maxLength(255),
                    TextInput::make('student_email')
                        ->label('Student email')
                        ->email()
                        ->required(fn (Get $get): bool => blank($get('user_id')))
                        ->maxLength(255),
                    TextInput::make('student_phone')
                        ->label('Student phone')
                        ->tel()
                        ->required(fn (Get $get): bool => blank($get('user_id')))
                        ->maxLength(30),
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('Linked user (optional)')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->placeholder('— Select —')
                        ->helperText('Selecting a registered user copies their name, email and phone into the snapshot below. The snapshot stays editable and is never written back to the user.')
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?string $old): void {
                            BuyerSnapshotAutofill::apply($get, $set, $old, $state);
                        }),
                ]),

            Section::make('Order Details')
                ->description('The package sold, the agreed price and how much has been received so far.')
                ->columns(2)
                ->schema([
                    Select::make('service_id')
                        ->relationship('service', 'name')
                        ->label('Package')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('Guided Application, Full Application Service or Elite Success Program. Locked on edit — the canonical order item stays pinned to the original package.'),
                    TextInput::make('amount')
                        ->label('Total amount (৳)')
                        ->required()
                        ->numeric()
                        ->prefix('৳')
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (callable $get, callable $set): void {
                            self::syncAmountDue($get, $set);
                        })
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('Total order value in Bangladeshi Taka. Money fields are locked on edit — record further payments through the list actions.'),
                    TextInput::make('amount_paid')
                        ->label('Amount paid (৳)')
                        ->required()
                        ->numeric()
                        ->prefix('৳')
                        ->minValue(0)
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (callable $get, callable $set): void {
                            self::syncAmountDue($get, $set);
                        })
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('Amount received so far. Money fields are locked on edit — record further payments through the list actions.'),
                    TextInput::make('amount_due')
                        ->label('Amount due (৳)')
                        ->numeric()
                        ->prefix('৳')
                        ->minValue(0)
                        ->default(0)
                        ->disabled()
                        ->helperText('Remaining balance — auto-calculated as total minus amount received, never typed.'),
                ]),

            Section::make('Payment Info')
                ->description('How the student paid. The status must agree with the money received; further payments are recorded through the list actions.')
                ->columns(2)
                ->schema([
                    Select::make('payment_method')
                        ->label('Payment method')
                        ->options(ServiceOrder::PAYMENT_METHODS)
                        ->placeholder('— Select —')
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('bKash, Nagad or Bank Transfer for offline sales; Cash when collected in person. Locked on edit.'),
                    Select::make('payment_status')
                        ->label('Payment status')
                        ->options(fn (Get $get, string $operation): array => self::paymentStatusOptions($get, $operation))
                        ->rules(fn (Get $get, string $operation): array => $operation === 'edit' ? [] : [
                            'in:'.implode(',', array_keys(self::paymentStatusOptions($get, 'create'))),
                            function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                self::validatePaymentStatusCoherence($value, $get, $fail);
                            },
                        ])
                        ->default('pending')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                            $enrollmentStatus = (string) ($get('enrollment_status') ?? 'pending');

                            if ($state === 'paid' && $enrollmentStatus === 'pending') {
                                $set('enrollment_status', 'in_progress');
                            } elseif ($state !== 'paid' && $enrollmentStatus === 'in_progress') {
                                $set('enrollment_status', 'pending');
                            }
                        })
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('Pending, Partially Paid or (when the full amount is already in) Paid. Status changes are locked on edit — use the list actions.'),
                ]),

            Section::make('Tracking')
                ->description('Internal order lifecycle — the customer never sees this.')
                ->columns(2)
                ->schema([
                    Select::make('enrollment_status')
                        ->label('Order status')
                        ->options(fn (Get $get, string $operation): array => self::enrollmentStatusOptions($get, $operation))
                        ->rules(fn (Get $get, string $operation): array => $operation === 'edit' ? [] : [
                            'in:'.implode(',', array_keys(self::enrollmentStatusOptions($get, 'create'))),
                            function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                if ($value === 'in_progress' && $get('payment_status') !== 'paid') {
                                    $fail('A service order only becomes In Progress once the payment is fully paid.');
                                }
                            },
                        ])
                        ->default('pending')
                        ->required()
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('Pending → In Progress (on full payment) → Completed (or Cancelled). Status changes are locked on edit.'),
                    Textarea::make('admin_notes')
                        ->label('Internal notes')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Private notes: payment reference, documents received, follow-ups, handover notes — never shown publicly.'),
                ]),
        ]);
    }

    /**
     * Entry states for a manual sale: pending, partially paid or already
     * fully paid. Refunded is a review/accounting outcome, never an entry
     * state. On edit the current value is kept (via includeCurrent) purely so
     * the disabled field still renders; the field is not dehydrated there.
     *
     * @return array<string, string>
     */
    protected static function paymentStatusOptions(Get $get, string $operation): array
    {
        $options = [
            'pending' => 'Pending',
            'partially_paid' => 'Partially Paid',
            'paid' => 'Paid',
        ];

        return $operation === 'edit'
            ? self::includeCurrent($options, $get('payment_status'), ServiceOrder::PAYMENT_STATUSES)
            : $options;
    }

    /**
     * A fully paid manual order starts In Progress (the same state the
     * mark-paid / record-payment actions produce). Pending or partially paid
     * orders start Pending (or Cancelled for a declined offer). Completed is
     * only reached through the mark-completed action once the service is done.
     *
     * @return array<string, string>
     */
    protected static function enrollmentStatusOptions(Get $get, string $operation): array
    {
        $payment = (string) ($get('payment_status') ?? 'pending');

        if ($operation === 'create') {
            return $payment === 'paid'
                ? ['in_progress' => ServiceOrder::ENROLLMENT_STATUSES['in_progress']]
                : ['pending' => 'Pending', 'cancelled' => 'Cancelled'];
        }

        $options = [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return self::includeCurrent($options, $get('enrollment_status'), ServiceOrder::ENROLLMENT_STATUSES);
    }

    /**
     * The status the admin chooses must agree with the money recorded, so the
     * mirrored canonical Payment ledger never contradicts the legacy row.
     */
    protected static function validatePaymentStatusCoherence(mixed $value, Get $get, \Closure $fail): void
    {
        if (! is_string($value) || ! in_array($value, ['pending', 'partially_paid', 'paid'], true)) {
            return;
        }

        $amount = (float) ($get('amount') ?? 0);
        $paid = (float) ($get('amount_paid') ?? 0);

        if ($value === 'pending' && $paid > 0.009) {
            $fail('An order with money already received must be recorded as Partially Paid or Paid, not Pending.');
        }

        if ($value === 'partially_paid' && ($paid <= 0.009 || $paid >= $amount - 0.009)) {
            $fail('Partially Paid requires a received amount between zero and the full total.');
        }

        if ($value === 'paid' && abs($paid - $amount) > 0.009) {
            $fail('Paid requires the full total to be received — update the received amount first.');
        }
    }

    /**
     * @param  array<string, string>  $options
     * @param  array<string, string>  $all
     * @return array<string, string>
     */
    protected static function includeCurrent(array $options, mixed $current, array $all): array
    {
        if (is_string($current) && $current !== '' && ! isset($options[$current]) && isset($all[$current])) {
            $options[$current] = $all[$current];
        }

        return $options;
    }

    protected static function syncAmountDue(callable $get, callable $set): void
    {
        $due = max(0, (float) $get('amount') - (float) $get('amount_paid'));

        $set('amount_due', number_format($due, 2, '.', ''));
    }
}
