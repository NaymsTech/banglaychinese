<?php

namespace App\Filament\Pages;

use App\Http\Controllers\UnifiedCheckoutController;
use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class CheckoutPageCms extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Checkout Page CMS';

    protected static ?int $navigationSort = 34;

    protected string $view = 'filament.pages.checkout-page-cms';

    public ?array $data = [];

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Content Management';
    }

    public function mount(): void
    {
        $this->form->fill([
            'checkout_intro' => Setting::where('key', 'checkout_intro_help')->value('value')
                ?? UnifiedCheckoutController::CHECKOUT_INTRO_DEFAULT,
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Textarea::make('checkout_intro')
                ->label('Checkout intro line')
                ->rows(3)
                ->helperText('Shown at the top of both the course and product checkout pages. Use {item} as a placeholder for the item type (e.g. "course"/"digital product"). Pricing and payment behaviour are never editable here.'),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::updateOrCreate(
            ['key' => 'checkout_intro_help'],
            ['value' => trim((string) ($data['checkout_intro'] ?? ''))]
        );

        Notification::make()
            ->success()
            ->title('Checkout content saved successfully.')
            ->send();
    }
}
