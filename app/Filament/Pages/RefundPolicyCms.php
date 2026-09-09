<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ManagesLegalCmsPage;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class RefundPolicyCms extends Page
{
    use ManagesLegalCmsPage;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Refund & Returns Policy';

    protected static ?int $navigationSort = 32;

    protected string $view = 'filament.pages.refund-policy-cms';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Content Management';
    }

    protected function pageTitle(): string
    {
        return 'Refund & Returns Policy';
    }

    protected function pageKey(): string
    {
        return 'refund-and-returns-policy';
    }
}
