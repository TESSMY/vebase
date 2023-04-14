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
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Vecapital\Vebase\Http\Controllers\VeController;

class ClientController extends VeController
{
    public function import(Request $request)
    {
        $this->authorize('create', Client::class);
        $input = $request->all();

        $validator = Validator::make($input, [
            'import_file' => 'required|file|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/excel',
        ]);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
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
        $this->authorize('viewAny', Client::class);

        try {
            return Excel::download(new ClientExport, 'Clients.xlsx');
        } catch (Exception $exception) {
            flash()->error('There was an issue exporting the clients. Message: ' . $exception->getMessage());
            return back();
        }
    }
}
