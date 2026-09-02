<?php

namespace App\Filament\Resources\ScholarshipApplications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScholarshipApplicationForm
{
    public const CRM_STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'consultation_scheduled' => 'Consultation Scheduled',
        'application_started' => 'Application Started',
        'converted' => 'Converted',
        'closed' => 'Closed',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(50),
                    ]),

                Section::make('Academic Background')
                    ->columns(2)
                    ->schema([
                        TextInput::make('highest_qualification')
                            ->placeholder('e.g. HSC, Bachelor of Science'),
                        TextInput::make('gpa_cgpa')
                            ->placeholder('e.g. GPA 4.80 / 5.00'),
                        TextInput::make('hsk_english_level')
                            ->placeholder('e.g. HSK 3, IELTS 6.0'),
                    ]),

                Section::make('Application Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('desired_program')
                            ->placeholder('e.g. MBBS, BSc Computer Science'),
                        Select::make('interested_service_id')
                            ->relationship('interestedService', 'name')
                            ->label('Interested service')
                            ->searchable()
                            ->preload(),
                        TextInput::make('target_intake')
                            ->placeholder('e.g. September 2026'),
                        TextInput::make('budget')
                            ->numeric()
                            ->prefix('৳')
                            ->minValue(0)
                            ->placeholder('0'),
                        TextInput::make('preferred_consultation_time')
                            ->placeholder('e.g. Weekdays after 5pm'),
                    ]),

                Section::make('Student Message')
                    ->description('Submitted by the student on the public site.')
                    ->schema([
                        Textarea::make('message')
                            ->disabled()
                            ->rows(4),
                    ]),

                Section::make('Admin Management')
                    ->description('Internal CRM pipeline and scholarship review outcome.')
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options(self::CRM_STATUSES)
                            ->default('new')
                            ->required(),
                        Select::make('application_status')
                            ->label('Scholarship outcome')
                            ->options([
                                'pending' => 'Pending (no decision)',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->placeholder('Pending (no decision)'),
                        DatePicker::make('follow_up_date')
                            ->label('Follow-up date'),
                        Textarea::make('admin_notes')
                            ->label('Internal notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
