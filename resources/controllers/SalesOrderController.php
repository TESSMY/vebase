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
    public function store(Request $request)
    {
        $this->authorize('create', SalesOrder::class);

        $input = $request->input();
        $input['created_by'] = Auth::id();
        $input['currency'] = 'SGD';
        $client = Client::find($input['client_id']);
        $input['customer_po'] = $client->address_1;

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create($input);

            $subtotal = 0;
            $totalCost = 0;

            if (!empty($input['products'])) {
                foreach ($input['products'] as $selectedProduct) {
                    if (!empty($selectedProduct['product_variant_id'])) {
                        $productVariant = ProductVariant::find($selectedProduct['product_variant_id']);
                        $product = $productVariant->product;

                        if (empty($productVariant)) {
                            flash('Error: Product variant with ID #' . $selectedProduct['product_variant_id'] . ' not found')->error();
                            return back();
                        }

                        if ($productVariant->status != ProductVariant::STATUS_ACTIVE || $productVariant->product->status != ProductVariant::STATUS_ACTIVE) {
                            flash('Error: Product variant with ID #' . $selectedProduct['product_variant_id'] . ' is not available')->error();
                            return back();
                        }

                        SalesOrderItem::create([
                            'sales_order_id' => $salesOrder->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $productVariant->id,
                            'product_name' => $product->name,
                            'sku' => $product->sku,
                            'description' => $product->description,
                            'quantity' => $selectedProduct['quantity'],
                            'unit_price' => $productVariant->selling_price,
                            'unit_cost' => $productVariant->cost_price,
                            'total_amount' => $selectedProduct['quantity'] * $productVariant->selling_price,
                            'total_cost' => $product['quantity'] * $productVariant->cost_price,
                        ]);

                        $subtotal += $selectedProduct['quantity'] * $productVariant->selling_price;
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

                        SalesOrderItem::create([
                            'sales_order_id' => $salesOrder->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'sku' => $product->sku,
                            'description' => $product->description,
                            'quantity' => $selectedProduct['quantity'],
                            'unit_price' => $product->selling_price,
                            'unit_cost' => $product->cost_price,
                            'total_amount' => $selectedProduct['quantity'] * $product->selling_price,
                            'total_cost' => $product['quantity'] * $product->cost_price,
                        ]);

                        $subtotal += $selectedProduct['quantity'] * $product->selling_price;
                        $totalCost += $selectedProduct['quantity'] * $product->cost_price;
                    }
                }
            }

            $salesOrder->tax_amount = $subtotal * ($input['tax_rate'] ?? 0) / 100;
            $salesOrder->tax_rate = $input['tax_rate'];
            $salesOrder->sub_total = $subtotal;
            $salesOrder->grand_total = $subtotal - $salesOrder->discount_amount + $salesOrder->tax_amount;
            $salesOrder->total_cost = $totalCost;
            $salesOrder->save();

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
        $input['created_by'] = Auth::id();
        $input['currency'] = 'SGD';
        $client = Client::find($input['client_id']);
        $input['customer_po'] = $client->address_1;

        $validator = Validator::make($input, $salesOrder->updateValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salesOrder->update($input);

            $subtotal = 0;
            $totalCost = 0;

            if (!empty($input['products'])) {
                $salesOrderItemIds = [];
                foreach ($input['products'] as $selectedProduct) {
                    if (!empty($selectedProduct['product_variant_id'])) {
                        $productVariant = ProductVariant::find($selectedProduct['product_variant_id']);
                        $product = $productVariant->product;

                        if (empty($productVariant)) {
                            flash('Error: Product variant with ID #' . $selectedProduct['product_variant_id'] . ' not found')->error();
                            return back();
                        }

                        if ($productVariant->status != ProductVariant::STATUS_ACTIVE || $productVariant->product->status != ProductVariant::STATUS_ACTIVE) {
                            flash('Error: Product variant with ID #' . $selectedProduct['product_variant_id'] . ' is not available')->error();
                            return back();
                        }

                        $salesOrderItem = $salesOrder->salesOrderItems()->where('sales_order_items.id', $selectedProduct['product_variant_id'])->first();

                        if (!empty($salesOrderItem)) {
                            $salesOrderItem->update([
                                'product_name' => $product->name,
                                'sku' => $product->sku,
                                'description' => $product->description,
                                'quantity' => $selectedProduct['quantity'],
                                'unit_price' => $productVariant->selling_price,
                                'unit_cost' => $productVariant->cost_price,
                                'total_amount' => $selectedProduct['quantity'] * $productVariant->selling_price,
                                'total_cost' => $product['quantity'] * $productVariant->cost_price,
                            ]);
                        } else {
                            $salesOrderItem = SalesOrderItem::create([
                                'sales_order_id' => $salesOrder->id,
                                'product_id' => $product->id,
                                'product_variant_id' => $productVariant->id,
                                'product_name' => $product->name,
                                'sku' => $product->sku,
                                'description' => $product->description,
                                'quantity' => $selectedProduct['quantity'],
                                'unit_price' => $productVariant->selling_price,
                                'unit_cost' => $productVariant->cost_price,
                                'total_amount' => $selectedProduct['quantity'] * $productVariant->selling_price,
                                'total_cost' => $product['quantity'] * $productVariant->cost_price,
                            ]);
                        }
                        $salesOrderItemIds[] = $salesOrderItem->id;
                        $subtotal += $selectedProduct['quantity'] * $productVariant->selling_price;
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

                        $salesOrderItem = $salesOrder->salesOrderItems()->where('sales_order_items.id', $selectedProduct['product_id'])->first();

                        if (!empty($salesOrderItem)) {
                            $salesOrderItem->update([
                                'product_name' => $product->name,
                                'sku' => $product->sku,
                                'description' => $product->description,
                                'quantity' => $selectedProduct['quantity'],
                                'unit_price' => $product->selling_price,
                                'unit_cost' => $product->cost_price,
                                'total_amount' => $selectedProduct['quantity'] * $product->selling_price,
                                'total_cost' => $product['quantity'] * $product->cost_price,
                            ]);
                        } else {
                            $salesOrderItem = SalesOrderItem::create([
                                'sales_order_id' => $salesOrder->id,
                                'product_id' => $product->id,
                                'product_name' => $product->name,
                                'sku' => $product->sku,
                                'description' => $product->description,
                                'quantity' => $selectedProduct['quantity'],
                                'unit_price' => $product->selling_price,
                                'unit_cost' => $product->cost_price,
                                'total_amount' => $selectedProduct['quantity'] * $product->selling_price,
                                'total_cost' => $product['quantity'] * $product->cost_price,
                            ]);
                        }
                        $salesOrderItemIds[] = $salesOrderItem->id;
                        $subtotal += $selectedProduct['quantity'] * $product->selling_price;
                        $totalCost += $selectedProduct['quantity'] * $product->cost_price;
                    }
                }
                $salesOrder->salesOrderItems()->whereNotIn('quotation_request_items.id', $salesOrderItemIds)->delete();

                $salesOrder->tax_amount = $subtotal * ($input['tax_rate'] ?? 0) / 100;
                $salesOrder->tax_rate = $input['tax_rate'];
                $salesOrder->sub_total = $subtotal;
                $salesOrder->grand_total = $subtotal - $salesOrder->discount_amount + $salesOrder->tax_amount;
                $salesOrder->total_cost = $totalCost;
                $salesOrder->save();
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
