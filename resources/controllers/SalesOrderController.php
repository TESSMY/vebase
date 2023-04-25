<?php

namespace App\Http\Controllers\Admin;

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

        $client = Client::find($input['client_id']);

        if (empty($client)) {
            flash()->error('Could not find the client selected. Please select a different client.');
            return back()->withInput($request->input());
        }

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            if (!empty($input['is_draft'])) {
                $input['status'] = SalesOrder::STATUS_DRAFT;
            } else {
                $input['status'] = SalesOrder::STATUS_ONGOING;
            }

            $salesOrder = SalesOrder::create($input);

            $this->updateOrCreateItem($salesOrder, $input['products']);
            $salesOrder->holdStock();

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

        if (!in_array($salesOrder->status, [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_OUTSTANDING])) {
            flash()->error("Can only update sales orders that are draft or outstanding.");
            return back();
        }

        DB::beginTransaction();
        try {
            if (!empty($input['is_draft'])) {
                $input['status'] = SalesOrder::STATUS_DRAFT;
            } else {
                $input['status'] = SalesOrder::STATUS_ONGOING;
            }

            $salesOrder->update($input);

            $this->updateOrCreateItem($salesOrder, $input['products']);
            $salesOrder->holdStock();

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
            $status = $salesOrder->status;

            $salesOrderItemIds = [];
            foreach ($selectedProducts as $selectedProduct) {
                if (!empty($selectedProduct['sales_order_item_id'])) {
                    // existing item
                    $salesOrderItem = $salesOrder->salesOrderItems()->find($selectedProduct['sales_order_item_id']);
                    $product = $salesOrderItem->product;
                    $productVariant = $salesOrderItem->productVariant;
                    $originalQuantity = $salesOrderItem->quantity;
                    $onHoldQuantity = $originalQuantity - $selectedProduct['quantity'];
                    $salesOrderItem->update([
                            'quantity' => $selectedProduct['quantity'],
                            'total_amount' => $selectedProduct['quantity'] * $salesOrderItem->unit_price,
                            'total_cost' => $selectedProduct['quantity'] * $salesOrderItem->unit_cost,
                    ]);
                    $salesOrderItemIds[] = $salesOrderItem->id;
                    $subTotal += $selectedProduct['quantity'] * $salesOrderItem->unit_price;
                    $totalCost += $selectedProduct['quantity'] * $salesOrderItem->unit_cost;

                    if ($salesOrder->status != SalesOrder::STATUS_DRAFT) {
                        if (!empty($productVariant)) {
                            if ($productVariant->available_stock < $selectedProduct['quantity']) {
                                $status = SalesOrder::STATUS_OUTSTANDING;
                                $salesOrderItem->status = SalesOrderItem::STATUS_OUTSTANDING;
                                $salesOrderItem->save();
                            }
                        } else {
                            if ($product->available_stock < $selectedProduct['quantity']) {
                                $status = SalesOrder::STATUS_OUTSTANDING;
                                $salesOrderItem->status = SalesOrderItem::STATUS_OUTSTANDING;
                                $salesOrderItem->save();
                            }
                        }
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

                        if ($salesOrder->status != SalesOrder::STATUS_DRAFT) {
                            if ($productVariant->available_stock < $selectedProduct['quantity']) {
                                $status = SalesOrder::STATUS_OUTSTANDING;
                                $salesOrderItem->status = SalesOrderItem::STATUS_OUTSTANDING;
                                $salesOrderItem->save();
                            }
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

                        if ($salesOrder->status == SalesOrder::STATUS_DRAFT) {
                            if ($product->available_stock < $selectedProduct['quantity']) {
                                $status = SalesOrder::STATUS_OUTSTANDING;
                                $salesOrderItem->status = SalesOrderItem::STATUS_OUTSTANDING;
                                $salesOrderItem->save();
                            }
                        }
                    }
                }
            }
            $salesOrder->salesOrderItems()->whereNotIn('sales_order_items.id', $salesOrderItemIds)->delete();
            $salesOrder->item_count = count($selectedProducts);
            $salesOrder->sub_total = $subTotal;
            $salesOrder->tax_amount = $salesOrder->sub_total * ($salesOrder->tax_rate ?? 0) / 100;
            $salesOrder->grand_total = $subTotal - $salesOrder->discount_amount + $salesOrder->tax_amount;
            $salesOrder->total_cost = $totalCost;
            $salesOrder->status = $status;
            $salesOrder->save();

            if ($salesOrder->status == SalesOrder::STATUS_OUTSTANDING) {
                flash('Error: There are product(s) within Sales Order ID: ' . $salesOrder->id . ' that do not have enough available stock. Sales Order has been updated to outstanding.')->error();
            }

            return $salesOrder;
        } catch (Exception $exception) {
            Log::error($exception);
            flash('There was an error creating the sales order item. Sales Order ID: '  . $salesOrder->id . '. Error: ' . $exception->getMessage());
            return back();
        }
    }

    public function generateOrder(Request $request, SalesOrder $salesOrder)
    {
        $this->authorize('view', $salesOrder);
        $input = $request->input();

        if (empty($salesOrder->salesOrderItems)) {
            flash()->error("Unable to generate order for sales orders with no items. Please add some items before generating an order.");
            return redirect()->route('admin.sales-orders.edit', $salesOrder->getRouteKey());
        }

        if (empty($input['delivery_order_products']) && empty($input['purchase_order_products'])) {
            flash()->error("Unable to generate order, please select at least one product.");
            return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
        }

        DB::beginTransaction();
        try {
            if (!empty($input['delivery_order_products'])) {
                $this->generateDo($salesOrder, $input['delivery_order_products'], $input['notes']);
                flash()->success('Successfully generated the delivery order(s).');
            }

            if (!empty($input['purchase_order_products'])) {
                $this->generatePo($salesOrder, $input['purchase_order_products'], $input['notes']);
                flash()->success('Successfully generated the purchase order(s).');
            }

            DB::commit();
            return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('There was an error creating the order(s). Sales Order ID: '  . $salesOrder->id . '. Error: ' . $exception->getMessage());
            return back();
        }
    }

    public function generateDo(SalesOrder $salesOrder, $products, $notes)
    {
        $this->authorize('view', $salesOrder);
        $this->authorize('create', DeliveryOrder::class);

        $subTotal = 0;

        $deliveryOrder = DeliveryOrder::create([
            'created_by' => $salesOrder->createdBy->id,
            'client_id' => $salesOrder->client->id,
            'sales_order_id' => $salesOrder->id,
            'client_name' => $salesOrder->ship_to_name,
            'client_address' => $salesOrder->ship_to_address_1,
            'postcode' => $salesOrder->ship_to_postcode,
            'country' => $salesOrder->ship_to_country,
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
            $salesOrderItem = $salesOrder->salesOrderItems()->find($product['sales_order_item_id']);

            if (empty($salesOrderItem)) {
                flash()->error("Unable to generate delivery order, could not find the sales order item. Sales Order Item ID: " . $product['sales_order_item_id']);
                return null;
            }

            $product = $salesOrderItem->product;
            $productVariant = $salesOrderItem->productVariant;

            if ($salesOrderItem->status == SalesOrderItem::STATUS_ORDER_CONFIRMED) { // outstanding item manually confirmed by admin
                if (!empty($productVariant)) {
                    if ($productVariant->available_stock < $salesOrderItem->quantity) {
                        flash()->error("Unable to generate delivery order, there is not enough product variant quantity available. Sales Order Item ID: " . $product['sales_order_item_id'] . ". Product Variant ID: " . $productVariant->id);
                        return null;
                    }
                } else {
                    if ($product->available_stock < $salesOrderItem->quantity) {
                        flash()->error("Unable to generate delivery order, there is not enough product quantity available. Sales Order Item ID: " . $product['sales_order_item_id'] . ". Product ID: " . $product->id);
                        return null;
                    }
                }
                $salesOrderItem->shipment_type = SalesOrderItem::SHIPMENT_TYPE_DIRECT_FROM_SELF;
                $salesOrderItem->save();
            }

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
            $subTotal += $salesOrderItem->total_amount;
        }

        $deliveryOrder->item_count = count($products);
        $deliveryOrder->tax_amount = $subTotal * $salesOrder->tax_rate / 100;
        $deliveryOrder->sub_total = $subTotal;
        $deliveryOrder->grand_total = $subTotal - $salesOrder->discount_amount + $salesOrder->tax_amount;
        $deliveryOrder->save();
    }

    public function generatePo(SalesOrder $salesOrder, $products, $notes)
    {
        $this->authorize('view', $salesOrder);
        $this->authorize('create', PurchaseOrder::class);

        $client = $salesOrder->client;

        if (empty($client)) {
            flash()->error("Unable to generate purchase order for sales orders with no client. Please select a client before generating a purchase order.");
            return null;
        }

        $subTotal = 0;
        $totalCost = 0;

        $products = collect($products)->groupBy('supplier_id');

        foreach ($products as $supplierId => $item) {
            $index = 0;
            $supplier = Supplier::find($supplierId);

            if (empty($supplier)) {
                flash()->error('Could not find the supplier for this sales order item. Sales Order Item ID: ' . $item[$index]['sales_order_item_id']);
                return null;
            }

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $supplierId,
                'client_id' => $salesOrder->client_id,
                'sales_order_id' => $salesOrder->id,
                'created_by' => $salesOrder->createdBy->id,
                'supplier_name' => $salesOrder->billing_name,
                'supplier_contact_number' => $salesOrder->billing_contact_number,
                'supplier_contact_email' => $salesOrder->billing_contact_email,
                'supplier_address_1' => $salesOrder->billing_address_1,
                'supplier_address_2' => $salesOrder->billing_address_2,
                'supplier_city' => $salesOrder->billing_city,
                'supplier_state' => $salesOrder->billing_state,
                'supplier_postcode' => $salesOrder->billing_postcode,
                'supplier_country' => $salesOrder->billing_country,
                'ship_to_name' => $salesOrder->ship_to_name,
                'ship_to_contact_number' => $salesOrder->ship_to_contact_number,
                'ship_to_contact_email' => $salesOrder->ship_to_contact_email,
                'ship_to_address_1' => $salesOrder->ship_to_address_1,
                'ship_to_address_2' => $salesOrder->ship_to_address_2,
                'ship_to_city' => $salesOrder->ship_to_city,
                'ship_to_state' => $salesOrder->ship_to_state,
                'ship_to_postcode' => $salesOrder->ship_to_postcode,
                'ship_to_country' => $salesOrder->ship_to_country,
                'currency' => $salesOrder->currency,
                'discount_amount' => 0,
                'shipping_handling' => 0,
                'other_cost' => 0,
                'tax_rate' => $salesOrder->tax_rate,
                'tax_amount' => 0,
                'sub_total' => $subTotal,
                'grand_total' => 0,
                'total_cost' => $totalCost,
                'total_paid' => 0,
                'supplier_code' => $supplier->code ?? null,
                'date' => now(),
                'notes' => $notes,
                'item_count' => 0,
                'shipment_type' => PurchaseOrder::SHIPMENT_TYPE_DIRECT_TO_CUSTOMER,
                'status' => PurchaseOrder::STATUS_DRAFT,
            ]);

            $itemCount = 0;
            foreach ($item as $product) {
                $salesOrderItem = $salesOrder->salesOrderItems()->find($product['sales_order_item_id']);

                if (empty($salesOrderItem)) {
                    flash()->error("Unable to generate purchase order, could not find the sales order item. Sales Order Item ID: " . $product['sales_order_item_id']);
                    return null;
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

        DB::beginTransaction();
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

                if (empty($salesOrderItem)) {
                    flash()->error("Unable to update sales order item, could not find the item associated in this sales order. Sales Order ID: " . $salesOrder->id . ". Sales Order Item ID: " . $product['sales_order_item_id']);
                    return back();
                }

                $salesOrderItem->update([
                    'status' => $product['status'],
                    'shipment_type' => SalesOrderItem::SHIPMENT_TYPE_DIRECT_FROM_SUPPLIER
                ]);
            }

            $approvedItemCount = $salesOrder->salesOrderItems()->where(function ($query) {
                $query->where('status', SalesOrderItem::STATUS_ORDER_CONFIRMED)
                    ->orWhere('status', SalesOrderItem::STATUS_REJECTED);
            })->count();

            if ($approvedItemCount == $salesOrder->salesOrderItems()->count()) { // all items are of approved or rejected status
                $salesOrder->status = SalesOrder::STATUS_ONGOING;
                $salesOrder->save();
            }

            $salesOrder->holdStock();

            DB::commit();
            flash()->success('Successfully updated the sales order items.');
            return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('There was an issue updating the sales order items. Sales Order ID: ' . $salesOrder->id . '. Error: ' . $exception->getMessage());
            return redirect()->route('admin.sales-orders.show', $salesOrder->getRouteKey());
        }
    }
}
