<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\CustomerOrderSummary;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Customer-level order history: one page showing a user's profile, their
 * lifetime financial summary (computed from canonical orders + payments)
 * and — via the resource's OrdersRelationManager — every canonical Order
 * belonging to them.
 */
class ViewCustomer extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function infolist(Schema $schema): Schema
    {
        $customer = $this->getRecord();
        $summary = CustomerOrderSummary::forUser($customer);

        return $schema->components([
            Section::make('Customer')->columns(3)->schema([
                TextEntry::make('customer_name')->label('Name')->state($customer->name),
                TextEntry::make('customer_email')->label('Email')->state($customer->email),
                TextEntry::make('customer_phone')->label('WhatsApp / phone')->state(filled($customer->phone) ? $customer->phone : '—'),
            ]),
            Section::make('Lifetime financial summary')->columns(3)->schema([
                TextEntry::make('summary_order_count')->label('Total orders')->state((string) $summary->orderCount),
                TextEntry::make('summary_total_value')->label('Total order value')->state(self::money($summary->totalValue)),
                TextEntry::make('summary_total_paid')->label('Total paid')->state(self::money($summary->totalPaid)),
                TextEntry::make('summary_total_refunded')->label('Total refunded')->state(self::money($summary->totalRefunded)),
                TextEntry::make('summary_total_due')->label('Total due')->state(self::money($summary->totalDue)),
                TextEntry::make('summary_pending')->label('Pending orders')->state((string) $summary->pendingCount),
                TextEntry::make('summary_partially_paid')->label('Partially paid orders')->state((string) $summary->partiallyPaidCount),
                TextEntry::make('summary_completed')->label('Completed orders')->state((string) $summary->completedCount),
                TextEntry::make('summary_needs_attention')->label('Needing attention')->state((string) $summary->needsAttentionCount),
            ]),
        ]);
    }

    public function getTitle(): string
    {
        $name = trim((string) $this->getRecord()->name);

        return filled($name) ? "Customer: {$name}" : 'Customer';
    }

    protected static function money(float $amount): string
    {
        return '৳ '.number_format($amount, 2);
    }
}
