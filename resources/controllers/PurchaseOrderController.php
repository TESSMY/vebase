<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Vecapital\Vebase\Http\Controllers\VeController;

class PurchaseOrderController extends VeController
{
    public function store(Request $request)
    {
        $this->authorize('create', PurchaseOrder::class);
        $input = $request->input();
        $input['created_by'] = Auth::id();
        $supplier = Supplier::find($input['supplier_id']);

        if (empty($supplier)) {
            flash()->error('Could not find the supplier selected. Please select a different supplier.');
            return back()->withInput($request->input());
        }

        if ($input['shipment_type'] == PurchaseOrder::SHIPMENT_TYPE_DIRECT_TO_CUSTOMER) {
            $client = Client::find($input['client_id']);

            if (empty($client)) {
                flash()->error('Could not find the client selected. Please select a different client.');
                return back()->withInput($request->input());
            }
        }

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::create($input);

            $this->updateOrCreateItem($purchaseOrder, $input['products']);

            $purchaseOrder->tax_amount = $purchaseOrder->sub_total * ($input['tax_rate'] ?? 0) / 100;
            $purchaseOrder->file_url = $purchaseOrder->generatePdf();
            $purchaseOrder->save();

            DB::commit();
            flash()->success('Successfully created the purchase order.');
            return redirect()->route('admin.purchase-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception->getMessage());
            return redirect()->route('admin.purchase-orders.create')->withInput($request->input());
        }
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = $this->findModel($id);
        $this->authorize('update', $purchaseOrder);
        $input = $request->input();

        $validator = Validator::make($input, $this->model->updateValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->update($input);

            $this->updateOrCreateItem($purchaseOrder, $input['products']);

            $purchaseOrder->tax_amount = $purchaseOrder->sub_total * ($input['tax_rate'] ?? 0) / 100;
            $purchaseOrder->file_url = $purchaseOrder->generatePdf();
            $purchaseOrder->save();

            DB::commit();
            flash()->success('Successfully updated the purchase order.');
            return redirect()->route('admin.purchase-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception->getMessage());
            return redirect()->route('admin.purchase-orders.edit', $purchaseOrder->getRouteKey())->withInput($request->input());
        }
    }

    public function send(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder);

        try {
            $data["email"] = $request->input('to_email');
            $data["title"] = 'Purchase Order' . ' ' . $purchaseOrder->id;
            $data["purchaseOrder"] = $purchaseOrder;
            Mail::send('admin.purchase-orders.message', $data, function ($message) use ($data, $purchaseOrder) {
                $message->to($data["email"], $data["email"])
                        ->subject($data["title"])
                        ->attach(Storage::url($purchaseOrder->file_url));
            });

            $purchaseOrder->status = PurchaseOrder::STATUS_SENT;
            $purchaseOrder->save();

            flash()->success('Mail sent successfully!');
            return redirect()->route('admin.purchase-orders.index');
        } catch(Exception $exception) {
            Log::error('There was an issue sending the sending the pdf. Purchase Order ID: ' . $purchaseOrder->id . ' . Error: ' . $exception->getMessage());
            flash()->error('There was an issue sending the sending the pdf. Purchase Order ID: ' . $purchaseOrder->id . ' . Error: ' . $exception->getMessage());
            return redirect()->route('admin.purchase-orders.index');
        }

    }

    public function updateOrCreateItem(PurchaseOrder $purchaseOrder, $selectedProducts)
    {
        if (empty($selectedProducts)) {
            flash()->error('Selected products is empty, please add a product in order to create purchase order items. Purchase Order ID: ' . $purchaseOrder->id);
            return back();
        }

        try {
            $subTotal = 0;
            $totalCost = 0;

            $purchaseOrderItemIds = [];
            foreach ($selectedProducts as $selectedProduct) {
                if (!empty($selectedProduct['purchase_order_item_id'])) {
                    // existing item
                    $purchaseOrderItem = $purchaseOrder->purchaseOrderItems()->find($selectedProduct['purchase_order_item_id']);
                    $purchaseOrderItem->update([
                            'quantity' => $selectedProduct['quantity'],
                            'total_amount' => $selectedProduct['quantity'] * $purchaseOrderItem->unit_price,
                            'total_cost' => $selectedProduct['quantity'] * $purchaseOrderItem->unit_cost,
                    ]);
                    $purchaseOrderItemIds[] = $purchaseOrderItem->id;
                    $subTotal += $selectedProduct['quantity'] * $purchaseOrderItem->unit_price;
                    $totalCost += $selectedProduct['quantity'] * $purchaseOrderItem->unit_cost;
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

                        $purchaseOrderItem = PurchaseOrderItem::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $productVariant->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'description' => $product->description,
                            'quantity' => $selectedProduct['quantity'],
                            'unit_price' => $productVariant->selling_price,
                            'unit_cost' => $productVariant->cost_price,
                            'total_amount' => $selectedProduct['quantity'] * $productVariant->selling_price,
                            'total_cost' => $selectedProduct['quantity'] * $productVariant->cost_price,
                        ]);

                        $purchaseOrderItemIds[] = $purchaseOrderItem->id;
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

                        $purchaseOrderItem = PurchaseOrderItem::create([
                            'purchase_order_id' => $purchaseOrder->id,
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

                        $purchaseOrderItemIds[] = $purchaseOrderItem->id;
                        $subTotal += $selectedProduct['quantity'] * $product->selling_price;
                        $totalCost += $selectedProduct['quantity'] * $product->cost_price;
                    }
                }
            }
            $purchaseOrder->purchaseOrderItems()->whereNotIn('purchase_order_items.id', $purchaseOrderItemIds)->delete();

            $purchaseOrder->item_count = count($selectedProducts);
            $purchaseOrder->sub_total = $subTotal;
            $purchaseOrder->grand_total = $subTotal - $purchaseOrder->discount_amount + $purchaseOrder->tax_amount;
            $purchaseOrder->total_cost = $totalCost;

            return $purchaseOrder;
        } catch (Exception $exception) {
            Log::error($exception);
            flash('There was an error creating the purchase order item. Purchase Order ID: '  . $purchaseOrder->id . '. Error: ' . $exception->getMessage());
            return back();
        }
    }
}
