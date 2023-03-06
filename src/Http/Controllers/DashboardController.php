<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public function index()
    {
        $purchaseOrderThisMonth = \App\Models\DeliveryOrder::whereMonth('created_at', date('m'))->sum('grant_total');
        $purchaseOrderLastMonth = \App\Models\DeliveryOrder::whereMonth('created_at', date('m', strtotime('-1 month')))->sum('grant_total');

        $purchaseOrderStatics = [
            'total' => (float) $purchaseOrderThisMonth,
            'growth' => $purchaseOrderLastMonth ? ($purchaseOrderThisMonth - $purchaseOrderLastMonth) / $purchaseOrderLastMonth : 100
        ];

        $shipmentStatics = [
            'total' => 36254,
            'growth' => -7.00
        ];

        $revenueStatics = [
            'total' => 6254,
            'growth' => 1.4
        ];

        $salesOrderThisMonth = \App\Models\SalesOrder::whereMonth('created_at', date('m'))->sum('grant_total');
        $salesOrderLastMonth = \App\Models\SalesOrder::whereMonth('created_at', date('m', strtotime('-1 month')))->sum('grant_total');
        $salesOrderStatics = [
            'total' => (float) $salesOrderThisMonth,
            'growth' => $salesOrderLastMonth ? ($purchaseOrderThisMonth - $salesOrderThisMonth) / $salesOrderLastMonth : 100
        ];

        $monthlyOrders = \App\Models\SalesOrder::select([DB::raw("SUM(grant_total) as total"), DB::raw("CONCAT(YEAR(created_at),MONTH(created_at)) as month")])
            ->whereRaw(DB::raw('CONCAT(YEAR(created_at),MONTH(created_at)) >= ?'), date('Ym', strtotime('-1 year')))
            ->orderBy('created_at')
            ->groupBy('month')
            ->get()
            ->map(function ($item) {
                $item->total = (float) $item->total;
                return $item;
            });

        $purchaseOrders = \App\Models\PurchaseOrder::with('supplier')->limit(10)->orderBy('created_at', 'desc')->get();
        $salesOrders = \App\Models\SalesOrder::limit(10)->orderBy('created_at', 'desc')->get();

        return View::make('vebase::admin.dashboard', compact(
            'purchaseOrderStatics',
            'shipmentStatics',
            'revenueStatics',
            'salesOrderStatics',
            'monthlyOrders',
            'purchaseOrders',
            'salesOrders'
        ));
    }
}
