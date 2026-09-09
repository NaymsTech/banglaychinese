<?php

namespace App\Filament\Resources\DigitalOrders\Schemas;

use App\Models\DigitalOrder;
use App\Support\BuyerSnapshotAutofill;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class DigitalOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Student Details')
                ->description('Who bought the product — link their account when they have one.')
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
                ->description('The product sold and the total amount of the sale. Digital products are delivered only after the full amount is received.')
                ->columns(2)
                ->schema([
                    Select::make('product_id')
                        ->relationship('product', 'title')
                        ->label('Product')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->helperText('The digital product the student is buying.'),
                    TextInput::make('amount')
                        ->label('Amount (৳)')
                        ->required()
                        ->numeric()
                        ->prefix('৳')
                        ->minValue(0)
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('Total order value. Digital products require full settlement before the download unlocks; the amount is locked on edit.'),
                    TextInput::make('trx_id')
                        ->label('Transaction ID')
                        ->placeholder('e.g. 9JQ2A3B4C5')
                        ->maxLength(255)
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('bKash / Nagad transaction reference sent by the student. Locked on edit — resubmission through the dashboard/student flow updates it.'),
                ]),

            Section::make('Review')
                ->description('Orders are always created as Pending. A fully paid offline sale is approved by recording the payment or using the approve action on the list — both queue the download email and unlock the product.')
                ->schema([
                    Select::make('status')
                        ->label('Order status')
                        ->options([DigitalOrder::STATUS_PENDING => 'Pending'])
                        ->default(DigitalOrder::STATUS_PENDING)
                        ->required()
                        ->visible(fn (string $operation): bool => $operation === 'create')
                        ->helperText('New orders start as Pending and are reviewed manually.'),
                ]),
        ]);
    }
}
