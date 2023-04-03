<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SupplierExport;
use App\Http\Controllers\Controller;
use App\Imports\SupplierImport;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Client;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Vecapital\Vebase\Http\Controllers\VeController;

class SupplierController extends VeController
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Supplier::class);

        $search = $request->input('search');
        $limit = $request->input('limit') ?? 10;
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');

        $suppliers = $this->model::query();

        if (!empty($search)) {
            if (!empty($this->model->searchable)) {
                $suppliers = $suppliers->where(function($query) use ($search) {
                    foreach ($this->model->searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%' . $search . '%');
                    }
                });
            }
        }

        if (!empty($orderColumn) && in_array($orderColumn, $this->model->sortable)) {
            $suppliers = $suppliers->orderBy($orderColumn, $orderBy);
        }

        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'oldest'){
            $suppliers->oldest();
        } elseif ($sortBy === 'latest'){
            $suppliers->latest();
        }

        $suppliers = $suppliers->paginate($limit)->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function import(Request $request)
    {
        $this->authorize('import', Supplier::class);

        if (empty(request()->file('import_file'))) {
            flash()->error('Please upload an import file excel spreadsheet in order to import rows.');
            return back()->withInput();
        }

        try {
            Excel::import(new SupplierImport, request()->file('import_file'));
            flash()->success('Successfully imported rows.');
            return redirect()->route('admin.suppliers.index');
        } catch (Exception $exception) {
            Log::error('There was an issue importing the excel. Message: ' . $exception->getMessage());
            flash()->error('There was an issue importing the excel. Message: ' . $exception->getMessage());
            return redirect()->route('admin.suppliers.index');
        }
    }

    public function export()
    {
        $this->authorize('export', Supplier::class);

        try {
            return Excel::download(new SupplierExport, 'Suppliers.xlsx');
        } catch (Exception $exception) {
            flash()->error('There was an issue exporting the suppliers. Message: ' . $exception->getMessage());
            return back();
        }

    }
}
