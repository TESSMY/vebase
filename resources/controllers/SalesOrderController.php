<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
use Vecapital\Vebase\Http\Controllers\VeController;

class SalesOrderController extends VeController
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', SalesOrder::class);

        $search = $request->input('search');
        $limit = $request->input('limit') ?? 10;
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');

        $salesOrders = $this->model::query();

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

        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'oldest'){
            $salesOrders->oldest();
        } elseif ($sortBy === 'latest'){
            $salesOrders->latest();
        }

        $salesOrders = $salesOrders->paginate($limit)->withQueryString();

        return view('admin.sales-orders.index', compact('salesOrders'));
    }

    public function edit(Request $request, $id)
    {
        $salesOrder = $this->findModel($id);
        $this->authorize('update', $salesOrder);

        $salesOrder = $salesOrder->load('salesOrderItems.product', 'salesOrderItems.productVariant', 'client');

        return view('admin.sales-orders.edit', compact('salesOrder'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', SalesOrder::class);

        $input = $request->input();
        $input['created_by'] = Auth::id();
        $input['currency'] = 'SGD';

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create($input);

            $this->updateOrCreateItem($salesOrder, $input['products'], $input['tax_rate'] ?? 0);

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

    public function update(Request $request, $id)
    {
        $salesOrder = $this->findModel($id);
        $this->authorize('update', $salesOrder);
        $input = $request->input();

        $validator = Validator::make($input, $salesOrder->updateValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salesOrder->update($input);

            $this->updateOrCreateItem($salesOrder, $input['products'], $input['tax_rate'] ?? 0);

            DB::commit();
            flash()->success('Successfully updated the sales order.');

            return redirect()->route('admin.sales-orders.edit', [$salesOrder->getRouteKey()]);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception);
            return redirect()->route('admin.sales-orders.edit', $salesOrder->getRouteKey())->withInput($request->input());
        }
    }

    public function updateOrCreateItem(SalesOrder $salesOrder, $selectedProducts, $taxRate = 0)
    {
        if (empty($selectedProducts)) {
            flash()->error('Selected products is empty, please add a product in order to create sales order items. Sales Order ID: ' . $salesOrder->id);
            return back();
        }

        try {
            $subTotal = 0;
            $totalCost = 0;

            foreach ($selectedProducts as $selectedProduct) {
                if (!empty($selectedProduct['sales_order_item_id'])) {
                    // existing item
                    $productModel = Product::find($selectedProduct['product_id']);

                    if (!empty($selectedProduct['product_variant_id'])) {
                        $productVariantModel = ProductVariant::find($selectedProduct['product_variant_id']);
                    }

                    $salesOrderItem = $salesOrder->salesOrderItems()->find($selectedProduct['sales_order_item_id']);
                    $salesOrderItem->update($selectedProduct + [
                            'product_variant_id' => !empty($productVariantModel) ? $productVariantModel->id : null,
                            'name' => $productModel->name,
                            'sku' => $productModel->sku,
                            'description' => $productModel->description,
                            'quantity' => $selectedProduct['quantity'],
                            'unit_price' => $productModel->selling_price,
                            'unit_cost' => $productModel->cost_price,
                            'total_amount' => $selectedProduct['quantity'] * $productModel->selling_price,
                            'total_cost' => $selectedProduct['quantity'] * $productModel->cost_price,
                    ]);
                    $salesOrderItemIds[] = $salesOrderItem->id;
                    $subTotal += $selectedProduct['quantity'] * (!empty($productVariantModel) ? $productVariantModel->selling_price : $productModel->selling_price);
                    $totalCost += $selectedProduct['quantity'] * (!empty($productVariantModel) ? $productVariantModel->cost_price : $productModel->cost_price);
                } else {
                    if (!empty($selectedProduct['product_variant_id'])) {
                        $productVariant = ProductVariant::find($selectedProduct['product_variant_id']);
                        $product = $productVariant->product;

                        if (empty($productVariant)) {
                            flash('Error: Product variant with ID #' . $selectedProduct['product_variant_id'] . ' not found')->error();
                            return back();
                        }

                        if ($productVariant->status != ProductVariant::STATUS_ACTIVE || $product->status != Product::STATUS_ACTIVE) {
                            flash('Error: Product variant with ID #' . $selectedProduct['product_variant_id'] . ' is not available')->error();
                            return back();
                        }

                        $salesOrderItem = SalesOrderItem::create([
                            'sales_order_id' => $salesOrder->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $productVariant->id,
                            'name' => $product->name,
                            'sku' => $productVariant->sku,
                            'description' => $product->description,
                            'quantity' => $selectedProduct['quantity'],
                            'unit_price' => $productVariant->selling_price,
                            'unit_cost' => $productVariant->cost_price,
                            'total_amount' => $selectedProduct['quantity'] * $productVariant->selling_price,
                            'total_cost' => $selectedProduct['quantity'] * $productVariant->cost_price,
                        ]);

                        $salesOrderItemIds[] = $salesOrderItem->id;
                        $subTotal += $selectedProduct['quantity'] * $productVariant->selling_price;
                        $totalCost += $selectedProduct['quantity'] * $productVariant->cost_price;
                    } else {
                        $product = Product::find($selectedProduct['product_id']);

                        if (empty($product)) {
                            flash('Error: Product with ID #' . $selectedProduct['product_id'] . ' not found')->error();
                            return back();
                        }

                        if ($product->type != Product::TYPE_PRODUCT_BUNDLE) {
                            flash('Error: Product with ID #' . $selectedProduct['product_id'] . ' is not a product bundle')->error();
                            return back();
                        }

                        if ($product->status != Product::STATUS_ACTIVE) {
                            flash('Error: Product with ID #' . $selectedProduct['product_id'] . ' is not available')->error();
                            return back();
                        }

                        $salesOrderItem = SalesOrderItem::create([
                            'sales_order_id' => $salesOrder->id,
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'description' => $product->description,
                            'quantity' => $selectedProduct['quantity'],
                            'unit_price' => $product->selling_price,
                            'unit_cost' => $product->cost_price,
                            'total_amount' => $selectedProduct['quantity'] * $product->selling_price,
                            'total_cost' => $selectedProduct['quantity'] * $product->cost_price,
                        ]);

                        $salesOrderItemIds[] = $salesOrderItem->id;
                        $subTotal += $selectedProduct['quantity'] * $product->selling_price;
                        $totalCost += $selectedProduct['quantity'] * $product->cost_price;
                    }
                }
            }

            $salesOrder->item_count = count($selectedProducts);
            $salesOrder->tax_amount = $subTotal * $taxRate / 100;
            $salesOrder->tax_rate = $taxRate;
            $salesOrder->sub_total = $subTotal;
            $salesOrder->grand_total = $subTotal - $salesOrder->discount_amount + $salesOrder->tax_amount;
            $salesOrder->total_cost = $totalCost;
            $salesOrder->save();

            return $salesOrder;
        } catch (Exception $exception) {
            Log::error($exception);
            flash('There was an error creating the sales order item. Sales Order ID: '  . $salesOrder->id . '. Error: ' . $exception->getMessage());
            return back();
        }
    }
}
