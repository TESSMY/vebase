<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InventoryReportExport;
use App\Models\InventoryReport;
use App\Models\InventoryReportItem;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Vecapital\Vebase\Http\Controllers\VeController;

class InventoryReportController extends VeController
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', InventoryReport::class);

        $limit = $request->input('limit') ?? 10;
        $productVariants = ProductVariant::query();

        $productVariants->withSum(
            [
                'purchaseOrderItem as qtyOnOrder' => function ($query) {
                    $query->whereHas('purchaseOrder', function ($q) {
                        $q->where('status', PurchaseOrder::STATUS_ORDER_CONFIRMED);
                    });
                }
            ],
            'quantity'
        );

        $productVariants->withSum(
            [
                'salesOrderItem as qtyPendingOrder' => function ($query) {
                    $query->whereHas('salesOrder', function ($q) {
                        $q->where('status', SalesOrder::STATUS_DRAFT);
                    });
                }
            ],
            'quantity'
        );

        $productVariants->withSum(
            [
                'salesOrderItem as qtyBackOrder' => function ($query) {
                    $query->whereHas('salesOrder', function ($q) {
                        $q->where('status', SalesOrder::STATUS_PENDING);
                    });
                }
            ],
            'quantity'
        );

        if (!empty($search)) {
            if (!empty($this->model->searchable)) {
                $productVariants = $productVariants->where(function($query) use ($search) {
                    foreach ($this->model->searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%' . $search . '%');
                    }
                });
            }
        }

        $productVariants = $productVariants->paginate($limit)->withQueryString();

        $compact = [
            'productVariants' => $productVariants,
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
            'limit' => $limit,
        ];

        return view('admin.inventory-reports.index', $compact);
    }

    public function history(Request $request)
    {
        $this->authorize('viewAny', InventoryReport::class);

        $limit = $request->input('limit') ?? 10;
        $inventoryReport = InventoryReport::query();

        $inventoryReport = $inventoryReport->paginate($limit)->withQueryString();

        $compact = [
            'inventoryReports' => $inventoryReport,
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
            'limit' => $limit,
        ];

        return view('admin.inventory-reports.history', $compact);
    }

    public function generate(Request $request)
    {
        $this->authorize('edit', InventoryReport::class);

        $path = '/inventory-reports/inventory-report-export' . now()->toDateTimeString() . '.xlsx';
        Excel::store(new InventoryReportExport, $path);

        InventoryReport::create([
            'generated_by' => Auth::id(),
            'created_by_name' => Auth::getName(),
            'file_url' => Storage::url($path),
        ]);

        flash()->success(__('Successfully created inventory report. You can download it in the history page.'));
        return redirect()->route('admin.inventory-reports.index');
    }
}
