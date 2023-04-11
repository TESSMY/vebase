<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Supplier;
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

        $input['client_id'] = 1;
        $input['products'] = [
            [
                'product_id' => 1,
                'product_variant_id' => 1,
                'quantity' => 1,
            ]
        ];

        $client = Client::find($input['client_id']);

        if (empty($client)) {
            flash()->error('Could not find the client selected. Please select a different client.');
            return back()->withInput($request->input());
        }

        $input['client_name'] = $client->name;
        $input['client_address'] = $client->address_1 . ", " . $client->address_2 . ", " . $client->city . ", " . $client->state . ", " . $client->postcode . ", " . $client->country;
        $input['address_1'] = $client->address_1;
        $input['address_2'] = $client->address_2;
        $input['city'] = $client->city;
        $input['state'] = $client->state;
        $input['postcode'] = $client->postcode;
        $input['country'] = $client->country;

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salesOrder = SalesOrder::create($input);

            $this->updateOrCreateItem($salesOrder, $input['products']);

            if ($input['is_draft'] == 1) {
                $salesOrder->status = SalesOrder::STATUS_DRAFT;
            }

            $salesOrder->tax_amount = $salesOrder->sub_total * $input['tax_rate'] ?? 0 / 100;
            $salesOrder->tax_rate = $input['tax_rate'] ?? 0;
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

        $validator = Validator::make($input, $salesOrder->updateValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salesOrder->update($input);

            $this->updateOrCreateItem($salesOrder, $input['products']);

            if ($input['is_draft'] == 1) {
                $salesOrder->status = SalesOrder::STATUS_DRAFT;
            }

            $salesOrder->tax_amount = $salesOrder->sub_total * $input['tax_rate'] ?? 0 / 100;
            $salesOrder->tax_rate = $input['tax_rate'] ?? 0;
            $salesOrder->save();

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

    public function updateOrCreateItem(SalesOrder $salesOrder, $selectedProducts)
    {
        if (empty($selectedProducts)) {
            flash()->error('Selected products is empty, please add a product in order to create sales order items. Sales Order ID: ' . $salesOrder->id);
            return back();
        }

        try {
            $subTotal = 0;
            $totalCost = 0;
            $status = SalesOrder::STATUS_ONGOING;

            $salesOrderItemIds = [];
            foreach ($selectedProducts as $selectedProduct) {
                if (!empty($selectedProduct['sales_order_item_id'])) {
                    // existing item
                    $salesOrderItem = $salesOrder->salesOrderItems()->find($selectedProduct['sales_order_item_id']);
                    $product = $salesOrderItem->product;
                    $productVariant = $salesOrderItem->productVariant;
                    $salesOrderItem->update([
                            'quantity' => $selectedProduct['quantity'],
                            'total_amount' => $selectedProduct['quantity'] * $salesOrderItem->unit_price,
                            'total_cost' => $selectedProduct['quantity'] * $salesOrderItem->unit_cost,
                    ]);
                    $salesOrderItemIds[] = $salesOrderItem->id;
                    $subTotal += $selectedProduct['quantity'] * $salesOrderItem->unit_price;
                    $totalCost += $selectedProduct['quantity'] * $salesOrderItem->unit_cost;

                    if (!empty($productVariant)) {
                        if ($productVariant->available_stock < $selectedProduct['quantity']) {
                            $status = SalesOrder::STATUS_OUTSTANDING;
                        }
                    } elseif ($product->available_stock < $selectedProduct['quantity']) {
                        $status = SalesOrder::STATUS_OUTSTANDING;
                    }
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
                            'name' => $productVariant->name,
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

                        if ($productVariant->available_stock < $selectedProduct['quantity']) {
                            $status = SalesOrder::STATUS_OUTSTANDING;
                        }
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

                        if ($product->available_stock < $selectedProduct['quantity']) {
                            $status = SalesOrder::STATUS_OUTSTANDING;
                        }
                    }
                }
            }
            $salesOrder->salesOrderItems()->whereNotIn('sales_order_items.id', $salesOrderItemIds)->delete();
            $salesOrder->item_count = count($selectedProducts);
            $salesOrder->sub_total = $subTotal;
            $salesOrder->grand_total = $subTotal - $salesOrder->discount_amount + $salesOrder->tax_amount;
            $salesOrder->total_cost = $totalCost;
            $salesOrder->status = $status;
            $salesOrder->save();

            return $salesOrder;
        } catch (Exception $exception) {
            Log::error($exception);
            flash('There was an error creating the sales order item. Sales Order ID: '  . $salesOrder->id . '. Error: ' . $exception->getMessage());
            return back();
        }
    }

    public function generateOrder(Request $request, SalesOrder $salesOrder)
    {
        $this->authorize('create', SalesOrder::class);
        $input = $request->input();

        if (empty($salesOrder->salesOrderItems)) {
            flash()->error("Unable to generate order for sales orders with no items. Please add some items before generating an order.");
            return redirect()->route('admin.sales-orders.edit', $salesOrder->getRouteKey());
        }

        if (empty($input['delivery_order_products']) && empty($input['purchase_order_products'])) {
            flash()->error("Unable to generate order, please select at least one product.");
            return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
        }

        if (!empty($input['delivery_order_products'])) {
            $this->generateDo($salesOrder, $input['delivery_order_products'], $input['notes_and_instructions']);
        }

        if (!empty($input['purchase_order_products'])) {
            $this->generatePo($salesOrder, $input['purchase_order_products'], $input['notes_and_instructions']);
        }

        return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
    }

    public function generateDo(SalesOrder $salesOrder, $products, $notes)
    {
        $this->authorize('create', DeliveryOrder::class);

        try {
            DB::beginTransaction();

            $subTotal = 0;

            $deliveryOrder = DeliveryOrder::create([
                'created_by' => $salesOrder->createdBy->id,
                'client_id' => $salesOrder->client->id,
                'sales_order_id' => $salesOrder->id,
                'client_name' => $salesOrder->client->name,
                'client_address' => $salesOrder->client->address_1,
                'item_count' => 0,
                'tax_rate' => $salesOrder->tax_rate,
                'tax_amount' => 0,
                'sub_total' => $subTotal,
                'discount_amount' => 0,
                'grand_total' => 0,
                'date' => now(),
                'notes' => $notes,
                'status' => DeliveryOrder::STATUS_CREATED,
            ]);

            foreach ($products as $product) {
                $itemCount = 0;
                $salesOrderItem = $salesOrder->salesOrderItems()->find($product['sales_order_item_id']);

                if (empty($salesOrderItem)) {
                    flash()->error("Unable to generate delivery order, could not find the sales order item. Sales Order Item ID: " . $product['sales_order_item_id']);
                    return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
                }

                $product = $salesOrderItem->product;
                $productVariant = $salesOrderItem->productVariant;

                DeliveryOrderItem::create([
                    'delivery_order_id' => $deliveryOrder->id,
                    'sales_order_item_id' => $salesOrderItem->id,
                    'product_id' => $salesOrderItem->product->id,
                    'product_variant_id' => $salesOrderItem->productVariant->id,
                    'name' => !empty($productVariant) ? $productVariant->name : $product->name,
                    'sku' => !empty($productVariant) ? $productVariant->sku : $product->sku,
                    'quantity' => $salesOrderItem->quantity,
                    'unit_price' => $salesOrderItem->unit_price,
                    'total_amount' => $salesOrderItem->total_amount,
                    'status' => DeliveryOrderItem::STATUS_PENDING,
                ]);

                $salesOrderItem->status = SalesOrderItem::STATUS_PENDING_SHIPMENT;
                $salesOrderItem->save();
                $itemCount++;
                $subTotal += $salesOrderItem->total_amount;
            }

            $deliveryOrder->item_count = $itemCount;
            $deliveryOrder->tax_amount = $subTotal * $salesOrder->tax_rate / 100;
            $deliveryOrder->sub_total = $subTotal;
            $deliveryOrder->grand_total = $subTotal - $salesOrder->discount_amount + $salesOrder->tax_amount;
            $deliveryOrder->save();

            DB::commit();
            flash()->success('Successfully generated the delivery order(s).');
            return $deliveryOrder;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('There was an issue generating the delivery order. Error: ' . $exception->getMessage());
            return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
        }
    }

    public function generatePo(SalesOrder $salesOrder, $products, $notes)
    {
        $this->authorize('create', PurchaseOrder::class);

        $client = $salesOrder->client;

        if (empty($client)) {
            flash()->error("Unable to generate purchase order for sales orders with no client. Please select a client before generating a purchase order.");
            return redirect()->route('admin.sales-orders.edit', $salesOrder->getRouteKey());
        }

        try {
            DB::beginTransaction();

            $subTotal = 0;
            $totalCost = 0;

            $products = collect($products)->groupBy('supplier_id');

            foreach ($products as $supplierId => $item) {
                $index = 0;
                $supplier = Supplier::find($supplierId);

                if (empty($supplier)) {
                    flash()->error('Could not find the supplier for this sales order item. Sales Order Item ID: ' . $item[$index]['sales_order_item_id']);
                    return false;
                }

                $purchaseOrder = PurchaseOrder::create([
                    'supplier_id' => $supplierId,
                    'client_id' => $salesOrder->client_id,
                    'sales_order_id' => $salesOrder->id,
                    'created_by' => $salesOrder->createdBy->id,
                    'item_count' => 0,
                    'supplier_code' => $supplier->code ?? null,
                    'date' => now(),
                    'supplier_name' => $supplier->name,
                    'ship_from_address' => $supplier->address,
                    'ship_from_city' => $supplier->city,
                    'ship_from_state' => $supplier->state,
                    'ship_from_postcode' => $supplier->zip,
                    'ship_from_country' => $supplier->country,
                    'client_name' => $salesOrder->client_name,
                    'ship_to_address' => $salesOrder->address_1,
                    'ship_to_city' => $salesOrder->city,
                    'ship_to_state' => $salesOrder->state,
                    'ship_to_postcode' => $salesOrder->postcode,
                    'ship_to_country' => $salesOrder->country,
                    'tax_rate' => $salesOrder->tax_rate,
                    'discount_amount' => 0,
                    'shipping_handling' => 0,
                    'other_cost' => 0,
                    'tax_amount' => 0,
                    'sub_total' => $subTotal,
                    'grand_total' => 0,
                    'total_cost' => $totalCost,
                    'total_paid' => 0,
                    'notes_and_instructions' => $notes,
                    'status' => PurchaseOrder::STATUS_DRAFT,
                ]);

                $itemCount = 0;
                foreach ($item as $product) {
                    $salesOrderItem = $salesOrder->salesOrderItems()->find($product['sales_order_item_id']);

                    if (empty($salesOrderItem)) {
                        flash()->error("Unable to generate purchase order, could not find the sales order item. Sales Order Item ID: " . $product['sales_order_item_id']);
                        return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
                    }

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'sales_order_item_id' => $salesOrderItem->id,
                        'product_id' => $salesOrderItem->product->id,
                        'product_variant_id' => $salesOrderItem->productVariant->id ?? null,
                        'name' => $salesOrderItem->name,
                        'sku' => $salesOrderItem->sku,
                        'description' => $salesOrderItem->description,
                        'quantity' => $salesOrderItem->quantity,
                        'unit_price' => $salesOrderItem->unit_price,
                        'unit_cost' => $salesOrderItem->unit_cost,
                        'total_amount' => $salesOrderItem->total_amount,
                        'total_cost' => $salesOrderItem->total_cost,
                        'status' => PurchaseOrderItem::STATUS_PENDING,
                    ]);

                    $salesOrderItem->status = SalesOrderItem::STATUS_PENDING_SHIPMENT;
                    $salesOrderItem->save();
                    $itemCount++;
                    $subTotal += $salesOrderItem->total_amount;
                    $totalCost += $salesOrderItem->total_cost;
                }
                $index++;

                $purchaseOrder->item_count = $itemCount;
                $purchaseOrder->tax_amount = $subTotal * $salesOrder->tax_rate / 100;
                $purchaseOrder->sub_total = $subTotal;
                $purchaseOrder->grand_total = $subTotal - $salesOrder->discount_amount + $salesOrder->tax_amount;
                $purchaseOrder->total_cost = $totalCost;
                $purchaseOrder->save();
            }

            DB::commit();
            flash()->success('Successfully generated the purchase order(s).');
            return $purchaseOrder;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('There was an issue generating the purchase order. Error: ' . $exception->getMessage());
            return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
        }
    }

    public function updateItemStatus(Request $request, SalesOrder $salesOrder)
    {
        $this->authorize('update', $salesOrder);
        $input = $request->input();

        if (empty($salesOrder->salesOrderItems)) {
            flash()->error("Unable to update for sales orders with no items. Please add some items.");
            return redirect()->route('admin.sales-orders.edit', $salesOrder->getRouteKey());
        }

        if (empty($input['products'])) {
            flash()->error("Unable to update order, please select at least one product.");
            return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
        }

        try {
            foreach ($input['products'] as $product) {
                if (empty($product['sales_order_item_id'])) {
                    flash()->error("Unable to update sales order item, the sales order item is empty. Sales Order ID: " . $salesOrder->id);
                    return back();
                }

                if (!isset($product['status'])) {
                    flash()->error("Unable to update sales order item, the status is empty. Please select a valid status. Sales Order ID: " . $salesOrder->id);
                    return back();
                }

                $salesOrderItem = $salesOrder->salesOrderItems()->find($product['sales_order_item_id']);
                $salesOrderItem->update([
                    'status' => $product['status'],
                ]);
            }

            flash()->success('Successfully updated the sales order items.');
            return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
        } catch (Exception $exception) {
            Log::error($exception);
            flash('There was an issue updating the sales order items. Sales Order ID: ' . $salesOrder->id . '. Error: ' . $exception->getMessage());
            return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
        }
    }
}
