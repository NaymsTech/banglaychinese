<?php

namespace App\Filament\Actions;

use App\Models\Product;
use Filament\Actions\DeleteAction;

/**
 * Guarded delete for products, shared by the list row action and the edit
 * page header action.
 *
 * digital_orders.product_id is a restricted foreign key: orders are sales
 * records that must be kept, so a referenced product cannot be removed. The
 * admin gets a clear explanation instead of a raw SQL integrity error.
 */
class DeleteProductAction
{
    public static function make(): DeleteAction
    {
        return DeleteAction::make()
            ->modalDescription('This permanently removes the product from the catalogue. A product with existing digital orders cannot be deleted.')
            ->failureNotificationTitle('Product could not be deleted')
            ->failureNotificationBody('This product has existing digital orders that must be kept for order history. Unpublish the product instead if you no longer want to sell it.')
            ->action(function (Product $record, DeleteAction $action): void {
                if ($record->digitalOrders()->exists()) {
                    $action->failure();

                    return;
                }

                $record->delete();
                $action->success();
            });
    }
}
