<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;

class DeliveryOrderController extends VeController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', $this->model);
        
        $input = $request->input();

        if (empty($this->model->createValidator)) {
            flash('Error: createValidator is empty')->error();
            return back()->withInput($request->input()); 
        }

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }
        
        try {
            DB::beginTransaction();

            $client = Client::find($input['client_id']);
            $deliveryOrder = DeliveryOrder::create($input + ['client_name' => $client->name, 'client_address' => $client->address_1 . ' ' . $client->address_2, 'created_by' => Auth::id()]);
            $subTotal = 0;

            foreach ($input['products'] as $product) {

                if (isset($product['product_variant_id'])) {
                    // product variant & single product
                    $productVariant = ProductVariant::find($product['product_variant_id']);

                    if (empty($productVariant)) {
                        flash('Error: Product variant with ID #' . $product['product_variant_id'] . ' not found')->error();
                        return back();
                    }

                    if ($productVariant->status != ProductVariant::STATUS_ACTIVE || $productVariant->product->status != ProductVariant::STATUS_ACTIVE) {
                        flash('Error: Product variant with ID #' . $product['product_variant_id'] . ' is not available')->error();
                        return back();
                    }
                    
                    DeliveryOrderItem::create([
                        'delivery_order_id' => $deliveryOrder->id,
                        'product_id' => $productVariant->product_id, 
                        'product_variant_id' => $productVariant->id,
                        'name' => $productVariant->name, 
                        'sku' => $productVariant->sku, 
                        'quantity' => $product['quantity'], 
                        'unit_price' => $productVariant->selling_price, 
                        'total_price' => $productVariant->selling_price * $product['quantity'], 
                    ]);
                } else {
                    // product bundle
                    $productModel = Product::find($product['product_id']);

                    if (empty($productModel)) {
                        flash('Error: Product with ID #' . $product['product_id'] . ' not found')->error();
                        return back();
                    }
                    if ($productModel->type != Product::TYPE_PRODUCT_BUNDLE) {
                        flash('Error: Product with ID #' . $product['product_id'] . ' is not a product bundle')->error();
                        return back();
                    }

                    if ($productModel->status != Product::STATUS_ACTIVE) {
                        flash('Error: Product with ID #' . $product['product_id'] . ' is not available')->error();
                        return back();
                    }

                    InvoiceItem::create([
                        'invoice_id' => $deliveryOrder->id,
                        'product_id' => $productModel->id, 
                        'name' => $productModel->name, 
                        'sku' => $productModel->sku, 
                        'quantity' => $product['quantity'], 
                        'unit_price' => $productModel->cost_price, 
                        'total_price' => $productModel->cost_price * $product['quantity'], 
                    ]);
                }
                $subTotal += $deliveryOrderItem->total_price; 
            }

            $deliveryOrder->item_count = count($input['products']);
            $deliveryOrder->sub_total = $subTotal;
            $grandTotal = $deliveryOrder->sub_total;
            if (!empty($input['discount_amount'])) {
                $subTotal -= $input['discount_amount'];
            }
            if (!empty($input['tax_rate_1'])) {
                $tax1 = $subTotal * ($input['tax_rate_1'] / 100);
                $grandTotal += $tax1;
                $deliveryOrder->tax_amount_1 = $tax1;
            }
            if (!empty($input['tax_rate_2'])) {
                $tax2 = $subTotal * ($input['tax_rate_2'] / 100);
                $grandTotal += $tax2;
                $deliveryOrder->tax_amount_2 = $tax2;
            }
            $deliveryOrder->grand_total = $grandTotal;
            $deliveryOrder->save();
            $deliveryOrder->generatePDF();

            DB::commit();
            flash()->success('Successfully created delivery order');
            return redirect()->route('admin.delivery-orders.index');
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            flash()->error('There was an issue creating delivery order');
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $deliveryOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $deliveryOrder)
    {
        $deliveryOrder = $this->findModel($deliveryOrder);
        $this->authorize('update', $deliveryOrder);
        $deliveryOrder->load('client', 'items.product', 'items.productVariant', 'createdBy');

        $compact = [
            'invoice' => $deliveryOrder,
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
        ];

        return view('admin.delivery-orders.edit', $compact);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $deliveryOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $deliveryOrder)
    {
        $deliveryOrder = $this->findModel($deliveryOrder);

        $this->authorize('update', $deliveryOrder);

        $input = $request->input();

        if (empty($deliveryOrder->updateValidator())) {
            flash('Error: updateValidator is empty')->error();
            return back(); 
        }

        $validator = Validator::make($input, $deliveryOrder->updateValidator());

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        try {
            DB::beginTransaction();
            

            $client = Client::find($input['client_id']);
            $deliveryOrder->update($input + ['client_name' => $client->name, 'client_address' => $client->address_1 . ' ' . $client->address_2]);
            $subTotal = 0;

            $deliveryOrderItemIds = [];
            foreach ($input['products'] as $product) {
                if (!empty($product['invoice_item_id'])) {
                    // existing invoice item
                    $productModel = Product::find($product['product_id']);
                    $deliveryOrderItem = InvoiceItem::find($product['invoice_item_id']);
                    $deliveryOrderItem->update($product + [
                        'product_variant_id' => !empty($product['product_variant_id']) ? $product['invoice_item_id'] : null, 
                        'name' => $productModel->name,
                    ]);
                    $deliveryOrderItemIds[] = $deliveryOrderItem->id;
                } else {
                    if (isset($product['product_variant_id'])) {
                        // product variant & single product
                        $productVariant = ProductVariant::find($product['product_variant_id']);

                        if (empty($productVariant)) {
                            flash('Error: Product variant with ID #' . $product['product_variant_id'] . ' not found')->error();
                            return back();
                        }

                        if ($productVariant->status != ProductVariant::STATUS_ACTIVE || $productVariant->product->status != ProductVariant::STATUS_ACTIVE) {
                            flash('Error: Product variant with ID #' . $product['product_variant_id'] . ' is not available')->error();
                            return back();
                        }
    
                        $deliveryOrderItem = InvoiceItem::create([
                            'invoice_id' => $deliveryOrder->id,
                            'product_id' => $productVariant->product_id, 
                            'product_variant_id' => $productVariant->id,
                            'name' => $productVariant->name, 
                            'sku' => $productVariant->sku, 
                            'quantity' => $product['quantity'], 
                            'unit_price' => $productVariant->selling_price, 
                            'total_price' => $productVariant->selling_price * $product['quantity'], 
                        ]);
                        $deliveryOrderItemIds[] = $deliveryOrderItem->id;
                    } else {
                        // product bundle
                        $productModel = Product::find($product['product_id']);

                        if (empty($productModel)) {
                            flash('Error: Product with ID #' . $product['product_id'] . ' not found')->error();
                            return back();
                        }
                        if ($productModel->type != Product::TYPE_PRODUCT_BUNDLE) {
                            flash('Error: Product with ID #' . $product['product_id'] . ' is not a product bundle')->error();
                            return back();
                        }
    
                        if ($productModel->status != Product::STATUS_ACTIVE) {
                            flash('Error: Product with ID #' . $product['product_id'] . ' is not available')->error();
                            return back();
                        }
    
                        $deliveryOrderItem = InvoiceItem::create([
                            'invoice_id' => $deliveryOrder->id,
                            'product_id' => $productModel->id, 
                            'name' => $productModel->name,
                            'sku' => $productModel->sku,
                            'quantity' => $product['quantity'], 
                            'unit_price' => $productModel->cost_price, 
                            'total_price' => $productModel->cost_price * $product['quantity'], 
                        ]);
                        $deliveryOrderItemIds[] = $deliveryOrderItem->id;
                    }
                }
                $subTotal += $deliveryOrderItem->total_price;
            }
            $deliveryOrder->items()->whereNotIn('invoice_items.id', $deliveryOrderItemIds)->delete();

            $deliveryOrder->item_count = count($input['products']);
            $deliveryOrder->sub_total = $subTotal;
            $grandTotal = $deliveryOrder->sub_total;
            if (!empty($input['discount_amount'])) {
                $subTotal -= $input['discount_amount'];
            }
            if (!empty($input['tax_rate_1'])) {
                $tax1 = $subTotal * ($input['tax_rate_1'] / 100);
                $grandTotal += $tax1;
                $deliveryOrder->tax_amount_1 = $tax1;
            }
            if (!empty($input['tax_rate_2'])) {
                $tax2 = $subTotal * ($input['tax_rate_2'] / 100);
                $grandTotal += $tax2;
                $deliveryOrder->tax_amount_2 = $tax2;
            }
            $deliveryOrder->grand_total = $grandTotal;
            $deliveryOrder->save();
            $deliveryOrder->generatePDF();

            DB::commit();
            flash()->success('Successfully updated invoice');
            return redirect()->route('admin.invoices.index');
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            flash()->error('There was an issue updating invoice');
            return back()->withInput();
        }
    }
}
