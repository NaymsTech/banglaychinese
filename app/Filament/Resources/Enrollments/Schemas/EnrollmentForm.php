<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),
                Select::make('course_id')
                    ->relationship('course', 'title')
                    ->disabled(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Approved',
                        'cancelled' => 'Rejected',
                    ])
                    ->disabled(),
                Select::make('payment_method')
                    ->options([
                        'bkash' => 'bKash',
                        'nagad' => 'Nagad',
                    ])
                    ->disabled(),
                TextInput::make('transaction_id')
                    ->disabled(),
                TextInput::make('sender_number')
                    ->disabled(),
                TextInput::make('price_paid')
                    ->label('Price paid (৳)')
                    ->numeric()
                    ->prefix('৳')
                    ->disabled(),
                DateTimePicker::make('paid_at')
                    ->disabled(),
                Textarea::make('rejection_reason')
                    ->label('Rejection reason')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
