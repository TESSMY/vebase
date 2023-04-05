<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SupplierExport;
use App\Imports\SupplierImport;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Vecapital\Vebase\Http\Controllers\VeController;

class SupplierController extends VeController
{
    public function import(Request $request)
    {
        $this->authorize('create', Supplier::class);

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
        $this->authorize('viewAny', Supplier::class);

        try {
            return Excel::download(new SupplierExport, 'Suppliers.xlsx');
        } catch (Exception $exception) {
            flash()->error('There was an issue exporting the suppliers. Message: ' . $exception->getMessage());
            return back();
        }

    }
}
