<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ManagesLegalCmsPage;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class PrivacyPolicyCms extends Page
{
    use ManagesLegalCmsPage;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Privacy Policy';

    protected static ?int $navigationSort = 31;

    protected string $view = 'filament.pages.privacy-policy-cms';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Content Management';
    }

    protected function pageTitle(): string
    {
        return 'Privacy Policy';
    }

    protected function pageKey(): string
    {
        return 'privacy-policy';
    }
}
