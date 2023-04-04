<?php

namespace App\Observers;

use App\Models\InventoryAdjustment;
use App\Models\ProductVariant;

class InventoryAdjustmentObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the SalesOrder "created" event.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return void
     */
    public function created(InventoryAdjustment $inventoryAdjustment)
    {
        foreach ($inventoryAdjustment->adjustmentItems as $adjustmentItem) {
            if (!empty($adjustmentItem->product_variant_id)) {
                // single & variant products
                $adjustmentItem->productVariant->total_stock = $adjustmentItem->new_value;
                $adjustmentItem->productVariant->save();

                $adjustmentItem->product->total_stock = $adjustmentItem->product->variants->sum('total_stock');
                $adjustmentItem->product->save();
            } else {
                // bundles
                $adjustmentItem->product->total_stock = $adjustmentItem->new_value;
                $adjustmentItem->product->save();
            }
        }
    }
}
