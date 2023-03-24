<?php

namespace App\Observers;

use App\Models\Shipment;

class ShipmentObserver
{
    /**
     * Handle the Shipment "created" event.
     *
     * @param  \App\Models\Shipment  $shipment
     * @return void
     */
    public function created(Shipment $shipment)
    {
       $shipment->courier?->recalculateTotals();
    }

    /**
     * Handle the Shipment "updated" event.
     *
     * @param  \App\Models\Shipment  $shipment
     * @return void
     */
    public function updated(Shipment $shipment)
    {
        $shipment->courier?->recalculateTotals();
    }

    /**
     * Handle the Shipment "deleted" event.
     *
     * @param  \App\Models\Shipment  $shipment
     * @return void
     */
    public function deleted(Shipment $shipment)
    {
        $shipment->courier?->recalculateTotals();
    }

    /**
     * Handle the Shipment "restored" event.
     *
     * @param  \App\Models\Shipment  $shipment
     * @return void
     */
    public function restored(Shipment $shipment)
    {
        $shipment->courier?->recalculateTotals();
    }

    /**
     * Handle the Shipment "force deleted" event.
     *
     * @param  \App\Models\Shipment  $shipment
     * @return void
     */
    public function forceDeleted(Shipment $shipment)
    {
        $shipment->courier?->recalculateTotals();
    }
}
