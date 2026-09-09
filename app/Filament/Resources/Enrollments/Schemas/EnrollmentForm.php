<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use App\Models\Course;
use App\Models\Enrollment;
use App\Support\BuyerSnapshotAutofill;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Student Details')
                ->description('Who bought the course — link their account when they have one.')
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

            Section::make('Course & Payment')
                ->description('The course sold, the agreed price and how much has been received.')
                ->columns(2)
                ->schema([
                    Select::make('course_id')
                        ->relationship('course', 'title')
                        ->label('Course')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                            self::autofillCourseAmount($get, $set, $state);
                        })
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('The purchased course. Locked on edit — the canonical order item and fulfillment stay pinned to the original course.'),
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
                        ->helperText('Total course fee. Auto-filled from the selected course price; still editable for a negotiated offline price. Money fields are locked on edit — record payments through the list actions.'),
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
                        ->helperText('Amount received so far. Money fields are locked on edit — record payments through the list actions.'),
                    TextInput::make('amount_due')
                        ->label('Amount due (৳)')
                        ->numeric()
                        ->prefix('৳')
                        ->minValue(0)
                        ->default(0)
                        ->disabled()
                        ->helperText('Remaining balance — auto-calculated as total minus amount received.'),
                    Select::make('payment_method')
                        ->label('Payment method')
                        ->options(Enrollment::PAYMENT_METHODS)
                        ->placeholder('— Select —')
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('bKash, Nagad or Bank Transfer for offline sales; Cash when collected in person. Locked on edit.'),
                ]),

            Section::make('Status')
                ->description('Payment state and course access. A fully paid offline sale starts In Progress; everything else starts Pending (or Cancelled). Later transitions happen through the dedicated approve / reject / record-payment actions.')
                ->columns(2)
                ->schema([
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

                            if ($state === Enrollment::PAYMENT_STATUS_PAID && $enrollmentStatus === 'pending') {
                                $set('enrollment_status', 'in_progress');
                            } elseif ($state !== Enrollment::PAYMENT_STATUS_PAID && $enrollmentStatus === 'in_progress') {
                                $set('enrollment_status', 'pending');
                            }
                        })
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('Pending, Partially Paid or (for money already received in full) Paid. Review states such as Rejected stay action-only.'),
                    Select::make('enrollment_status')
                        ->label('Enrollment status')
                        ->options(fn (Get $get, string $operation): array => self::enrollmentStatusOptions($get, $operation))
                        ->rules(fn (Get $get, string $operation): array => $operation === 'edit' ? [] : [
                            'in:'.implode(',', array_keys(self::enrollmentStatusOptions($get, 'create'))),
                            function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                if ($value === 'in_progress' && $get('payment_status') !== Enrollment::PAYMENT_STATUS_PAID) {
                                    $fail('A course only becomes In Progress once the payment is fully paid.');
                                }
                            },
                        ])
                        ->default('pending')
                        ->required()
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('A fully paid enrollment starts In Progress; partial or pending payments stay Pending. Status changes are locked on edit.'),
                ]),

            Section::make('Tracking')
                ->description('Internal notes — the student never sees this.')
                ->schema([
                    Textarea::make('admin_notes')
                        ->label('Internal notes')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Private notes: payment reference, documents received, follow-ups — never shown publicly.'),
                ]),
        ]);
    }

    /**
     * Entry states for a manual sale: a new enrollment can be pending,
     * partially paid or already fully paid. Refunded / rejected /
     * needs-attention are review outcomes, so they are only ever reached
     * through the dedicated actions — never chosen on a form. On edit the
     * current value is kept (via includeCurrent) purely so the disabled
     * field still renders; the field is not dehydrated there.
     *
     * @return array<string, string>
     */
    protected static function paymentStatusOptions(Get $get, string $operation): array
    {
        $options = [
            Enrollment::PAYMENT_STATUS_PENDING => 'Pending',
            Enrollment::PAYMENT_STATUS_PARTIALLY_PAID => 'Partially Paid',
            Enrollment::PAYMENT_STATUS_PAID => 'Paid',
        ];

        return $operation === 'edit'
            ? self::includeCurrent($options, $get('payment_status'), Enrollment::PAYMENT_STATUSES)
            : $options;
    }

    /**
     * When an admin records a fully paid offline sale at creation time the
     * enrollment starts In Progress — the same state the approve /
     * record-payment actions produce, so access and payment never disagree.
     * A pending/partial payment always starts Pending (or Cancelled for a
     * declined offer). "completed" is not an entry state: it is only reached
     * through the mark-completed action once the course is actually finished.
     *
     * @return array<string, string>
     */
    protected static function enrollmentStatusOptions(Get $get, string $operation): array
    {
        $payment = (string) ($get('payment_status') ?? Enrollment::PAYMENT_STATUS_PENDING);

        if ($operation === 'create') {
            return $payment === Enrollment::PAYMENT_STATUS_PAID
                ? ['in_progress' => Enrollment::ENROLLMENT_STATUSES['in_progress']]
                : ['pending' => 'Pending', 'cancelled' => 'Cancelled'];
        }

        $options = $payment === Enrollment::PAYMENT_STATUS_PAID
            ? ['completed' => 'Completed', 'cancelled' => 'Cancelled']
            : ['pending' => 'Pending', 'cancelled' => 'Cancelled'];

        return self::includeCurrent($options, $get('enrollment_status'), Enrollment::ENROLLMENT_STATUSES);
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

        if ($value === Enrollment::PAYMENT_STATUS_PENDING && $paid > 0.009) {
            $fail('An enrollment with money already received must be recorded as Partially Paid or Paid, not Pending.');
        }

        if ($value === Enrollment::PAYMENT_STATUS_PARTIALLY_PAID && ($paid <= 0.009 || $paid >= $amount - 0.009)) {
            $fail('Partially Paid requires a received amount between zero and the full total.');
        }

        if ($value === Enrollment::PAYMENT_STATUS_PAID && abs($paid - $amount) > 0.009) {
            $fail('Paid requires the full total to be received — update the received amount first.');
        }
    }

    /**
     * Populate the total amount from the selected course's current price.
     *
     * Rule (predictable): every explicit Course selection sets the total to
     * that course's price. Once the admin edits the amount (e.g. a negotiated
     * offline price), nothing except another explicit course change touches it
     * again — typing amount_paid, switching the linked user, or toggling the
     * payment status never resets the amount.
     */
    protected static function autofillCourseAmount(Get $get, Set $set, ?string $courseId): void
    {
        if (! filled($courseId)) {
            return;
        }

        $course = Course::query()->find((int) $courseId);

        if ($course === null || $course->price === null) {
            return;
        }

        $set('amount', (string) $course->price);

        // Keep the derived due in sync with the refreshed total.
        self::syncAmountDue($get, $set);
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
