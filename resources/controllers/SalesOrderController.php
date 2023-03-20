<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;

class SalesOrderController extends VeController
{
    public function index(Request $request)
    {
        $this->authorize('view', SalesOrder::class);

        $search = $request->input('search');
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');

        $salesOrders = SalesOrder::query();

        if (!empty($search)) {
            if (!empty($this->model->searchable)) {
                $salesOrders = $salesOrders->where(function($query) use ($search) {
                    foreach ($this->model->searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%' . $search . '%');
                    }
                });
            }
        }

        if (!empty($orderColumn) && in_array($orderColumn, $this->model->sortable)) {
            $salesOrders = $salesOrders->orderBy($orderColumn, $orderBy);
        }

        $salesOrders = $salesOrders->latest()->paginate(10)->withQueryString();

        return view('admin.sales-orders.index', compact('salesOrders'));
    }

    public function create()
    {
        $this->authorize('create', SalesOrder::class);

        $taxRate = 7;
        return view('admin.sales-orders.create', compact('taxRate'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', SalesOrder::class);

        $input = $request->input();
        $input['user_id'] = Auth::id();
        $input['currency'] = 'SGD';

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create($input);

            if (!empty($input['products'])) {
                foreach ($input['products'] as $product) {
                    $productVariant = ProductVariant::find($product['product_variant_id']);
                    $product = $productVariant->product;

                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $productVariant->id,
                        'quantity' => $product['quantity'],
                        'unit_price' => $productVariant->selling_price,
                        'grand_total' => $product['quantity'] * $productVariant->selling_price,
                    ]);
                }
            }

            DB::commit();
            flash()->success('Successfully created the sales order.');

            return redirect()->route('admin.sales-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception->getMessage());
            return redirect()->route('admin.sales-orders.create')->withInput($request->input());
        }
    }

    public function edit(Request $request, $id)
    {
        $salesOrder = $this->findModel($id);
        $this->authorize('update', $salesOrder);
        $taxRate = 7;
        return view('admin.sales-orders.edit', compact('taxRate', 'salesOrder'));
    }

    public function update(Request $request, $id)
    {
        $salesOrder = $this->findModel($id);
        $this->authorize('update', $salesOrder);
        $input = $request->input();
        $input['user_id'] = Auth::id();
        $input['currency'] = 'SGD';
        $model = SalesOrder::first();

        $validator = Validator::make($input, $model->updateValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salesOrder->update($input);

            if (!empty($input['products'])) {
                $salesOrder->orderItems->delete();
                foreach ($input['products'] as $product) {
                    $productVariant = ProductVariant::find($product['product_variant_id']);
                    $product = $productVariant->product;

                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $productVariant->id,
                        'quantity' => $product['quantity'],
                        'unit_price' => $productVariant->selling_price,
                        'grand_total' => $product['quantity'] * $productVariant->selling_price,
                    ]);
                }
                $salesOrder->orderItems->save();
            }

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
}
