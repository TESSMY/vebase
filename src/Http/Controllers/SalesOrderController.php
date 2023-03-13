<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $salesOrders = SalesOrder::query();

        if (!empty($search)) {
            $salesOrders = $salesOrders->where(function ($query) use ($search) {
                $query->where('user_id', 'LIKE', '%' . $search . '%');
            });
        }

        $salesOrders = $salesOrders->latest()->paginate(10)->withQueryString();

        return view('admin.sales-orders.index', compact('salesOrders'));
    }

    public function create(Request $request)
    {
        $taxRate = 7;
        return view('admin.sales-orders.create', compact('taxRate'));
    }

    public function store(Request $request)
    {
        $input = $request->input();
        $input['issued_by'] = 'Admin';
        $input['currency'] = 'SGD';
        $model = SalesOrder::first();

        $validator = Validator::make($input, $model->createValidator);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                flash('Error: ' . $error)->error();
            }
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create($input);
            DB::commit();
            flash()->success('Successfully created the sales order.');

            return redirect()->route('admin.sales-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception);
            return redirect()->route('admin.sales-orders.create')->withInput($request->input());
        }
    }

    public function edit(Request $request, SalesOrder $salesOrder)
    {
        $taxRate = 7;
        return view('admin.sales-orders.edit', compact('taxRate', 'salesOrder'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $input = $request->input();
        $input['issued_by'] = 'Admin';
        $input['currency'] = 'SGD';
        $model = SalesOrder::first();

        $validator = Validator::make($input, $model->updateValidator);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                flash('Error: ' . $error)->error();
            }
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salesOrder->update($input);
            DB::commit();
            flash()->success('Successfully updated the sales order.');

            return redirect()->route('admin.sales-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception);
            return redirect()->route('admin.sales-orders.edit', $salesOrder->getRouteKey())->withInput($request->input());
        }
    }

    public function destroy(Request $request, SalesOrder $salesOrder)
    {
        try {
            $salesOrder->delete();
            flash()->success('Successfully deleted the sales order.');

            return redirect()->route('admin.sales-orders.index');
        } catch (Exception $exception) {
            Log::error($exception);
            flash('Error: ' . $exception);
            return redirect()->route('admin.sales-orders.index');
        }
    }
}
