<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ClientExport;
use App\Exports\SupplierExport;
use App\Imports\ClientImport;
use App\Imports\SupplierImport;
use App\Models\Client;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Vecapital\Vebase\Http\Controllers\VeController;

class ClientController extends VeController
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Supplier::class);

        $search = $request->input('search');
        $limit = $request->input('limit') ?? 10;
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');

        $clients = $this->model::query();

        if (!empty($search)) {
            if (!empty($this->model->searchable)) {
                $clients = $clients->where(function($query) use ($search) {
                    foreach ($this->model->searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%' . $search . '%');
                    }
                });
            }
        }

        if (!empty($orderColumn) && in_array($orderColumn, $this->model->sortable)) {
            $clients = $clients->orderBy($orderColumn, $orderBy);
        }

        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'oldest'){
            $clients->oldest();
        } elseif ($sortBy === 'latest'){
            $clients->latest();
        }

        $clients = $clients->paginate($limit)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function import(Request $request)
    {
        $this->authorize('import', Client::class);

        if (empty(request()->file('import_file'))) {
            flash()->error('Please upload an import file excel spreadsheet in order to import rows.');
            return back()->withInput();
        }

        try {
            Excel::import(new ClientImport, request()->file('import_file'));
            flash()->success('Successfully imported rows.');
            return redirect()->route('admin.clients.index');
        } catch (Exception $exception) {
            Log::error('There was an issue importing the excel. Message: ' . $exception->getMessage());
            flash()->error('There was an issue importing the excel. Message: ' . $exception->getMessage());
            return redirect()->route('admin.clients.index');
        }
    }

    public function export()
    {
        $this->authorize('export', Client::class);

        try {
            return Excel::download(new ClientExport, 'Clients.xlsx');
        } catch (Exception $exception) {
            flash()->error('There was an issue exporting the clients. Message: ' . $exception->getMessage());
            return back();
        }
    }
}
