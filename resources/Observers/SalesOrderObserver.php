<?php

namespace App\Observers;

use App\Models\SalesOrder;
use App\Models\SalesReport;
use App\Helpers\SalesReportHelper;

class SalesOrderObserver
{
    /**
     * Handle the SalesOrder "created" event.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return void
     */
    public function created(SalesOrder $salesOrder)
    {
        $salesReportHelper = new SalesReportHelper($salesOrder);

        $salesReportHelper->recalculateDaily();
        $salesReportHelper->recalculateWeekly();
        $salesReportHelper->recalculateMonthly();
        $salesReportHelper->recalculateYearly();
        $salesReportHelper->recalculateOverall();
    }

    /**
     * Handle the SalesOrder "updated" event.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return void
     */
    public function updated(SalesOrder $salesOrder)
    {
        $salesReportHelper = new SalesReportHelper($salesOrder);

        $salesReportHelper->recalculateDaily();
        $salesReportHelper->recalculateWeekly();
        $salesReportHelper->recalculateMonthly();
        $salesReportHelper->recalculateYearly();
        $salesReportHelper->recalculateOverall();
    }
}
