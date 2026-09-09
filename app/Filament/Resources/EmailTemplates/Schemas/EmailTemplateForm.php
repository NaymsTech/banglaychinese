<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->description('How this template is identified and used.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Template name')
                            ->placeholder('e.g. Product Download Ready')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('key')
                            ->label('Key')
                            ->placeholder('product_approved')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Used in code, e.g. \'product_approved\'. Cannot be changed after creation.')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->unique(table: EmailTemplate::class, column: 'key', ignoreRecord: true),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->maxLength(1000)
                            ->helperText('What this email is for and where it is used. Shown to admins only.')
                            ->columnSpanFull(),
                        Select::make('category')
                            ->options(EmailTemplate::CATEGORIES)
                            ->default(EmailTemplate::CATEGORY_NOTIFICATION)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive templates cannot be used to send. System-critical templates (email_verification, password_reset) are always forced active.'),
                    ]),

                Section::make('Sender Details (Optional)')
                    ->description('Leave blank to use the provider\'s default sender (e.g. info@banglaychinese.com).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('from_address')
                            ->label('From Email Address')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('e.g. no-reply@banglaychinese.com')
                            ->helperText('Must be @banglaychinese.com'),
                        TextInput::make('from_name')
                            ->label('From Name')
                            ->maxLength(255)
                            ->placeholder('e.g. Banglay Chinese Downloads'),
                    ]),

                Section::make('Content')
                    ->description('Supports {variable_name} placeholders that are replaced at send time. Every placeholder used below must be listed under Variables, except the settings placeholders {bkash_number}, {nagad_number}, {bank_details}, {whatsapp_number} and {contact_email} which are filled automatically.')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Subject')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Use {variable_name} for dynamic content, e.g. Hi {student_name}!')
                            ->columnSpanFull(),
                        RichEditor::make('body')
                            ->label('HTML body')
                            ->required()
                            ->helperText('Write the email body. Use {variable_name} placeholders for dynamic content.')
                            ->columnSpanFull(),
                        TagsInput::make('variables')
                            ->label('Variables')
                            ->placeholder('student_name')
                            ->helperText('List every variable used in the subject/body — e.g. student_name, product_title, download_link. A template that references an undeclared placeholder cannot be sent.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
