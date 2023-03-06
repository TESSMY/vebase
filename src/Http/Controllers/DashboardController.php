<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryOrder;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public function index()
    {
        $purchaseOrderThisMonth = DeliveryOrder::whereMonth('created_at', date('m'))->sum('grant_total');
        $purchaseOrderLastMonth = DeliveryOrder::whereMonth('created_at', now()->subMonth()->month)->sum('grant_total');

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

        $salesOrderThisMonth = SalesOrder::whereMonth('created_at', date('m'))->sum('grant_total');
        $salesOrderLastMonth = SalesOrder::whereMonth('created_at', date('m', strtotime('-1 month')))->sum('grant_total');
        $salesOrderStatics = [
            'total' => (float) $salesOrderThisMonth,
            'growth' => $salesOrderLastMonth ? ($purchaseOrderThisMonth - $salesOrderThisMonth) / $salesOrderLastMonth : 100
        ];

        $monthlyOrders = TopOrder::whereRaw(DB::raw('CONCAT(year, month) >= ?'), date('Ym', strtotime('-1 year')))
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->total = (float) $item->total;
                return $item;
            });

        $purchaseOrders = PurchaseOrder::with('supplier')->limit(10)->orderBy('created_at', 'desc')->get();
        $salesOrders = SalesOrder::limit(10)->orderBy('created_at', 'desc')->get();

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
