<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Vecapital\Vebase\Http\Controllers\VeController;

class SupplierController extends VeController
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $supplier)
    {
        $supplier = $this->findModel($supplier);

        $this->authorize('view', $supplier);

        return view('admin.suppliers.show', compact('supplier'));

    }
}
