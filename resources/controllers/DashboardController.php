<?php

namespace App\Http\Controllers\Admin;

use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\MonthlyReport;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $monthlyOrders = MonthlyReport::whereRaw(DB::raw('CONCAT(year, month) >= ?'), date('Ym', strtotime('-1 year')))
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                $item->total_cost = (float) $item->total_cost;
                return $item;
            });

        $purchaseOrders = PurchaseOrder::with('supplier')->limit(10)->orderBy('created_at', 'desc')->get();
        $salesOrders = SalesOrder::limit(10)->orderBy('created_at', 'desc')->get();

        return view('admin.dashboard.index', compact(
            'monthlyOrders',
            'purchaseOrders',
            'salesOrders'
        ));
    }
}
