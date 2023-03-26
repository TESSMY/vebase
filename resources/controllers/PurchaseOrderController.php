<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Vecapital\Vebase\Http\Controllers\VeController;

class PurchaseOrderController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', PurchaseOrder::class);
        $input = $request->input();
        $input['created_by'] = Auth::id();

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::create($input);

            $subtotal = 0;
            $totalCost = 0;

            if (!empty($input['products'])) {
                foreach ($input['products'] as $purchaseOrderProduct) {
                    if (!empty($purchaseOrderProduct['product_variant_id'])) {
                        $productVariant = ProductVariant::find($purchaseOrderProduct['product_variant_id']);
                        $product = $productVariant->product;

                        if (empty($productVariant)) {
                            flash('Error: Product variant with ID #' . $purchaseOrderProduct['product_variant_id'] . ' not found')->error();
                            return back();
                        }

                        if ($productVariant->status != ProductVariant::STATUS_ACTIVE || $productVariant->product->status != ProductVariant::STATUS_ACTIVE) {
                            flash('Error: Product variant with ID #' . $purchaseOrderProduct['product_variant_id'] . ' is not available')->error();
                            return back();
                        }

                        PurchaseOrderItem::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $productVariant->id,
                            'quantity' => $purchaseOrderProduct['quantity'],
                            'unit_price' => $productVariant->cost_price,
                            'grand_total' => $purchaseOrderProduct['quantity'] * $productVariant->cost_price,
                        ]);
                        $subtotal += $purchaseOrderProduct['quantity'] * $productVariant->selling_price;
                        $totalCost += $purchaseOrderProduct['quantity'] * $productVariant->cost_price;
                    } else {
                        $product = Product::find($purchaseOrderProduct['product_id']);

                        if (empty($product)) {
                            flash('Error: Product with ID #' . $purchaseOrderProduct['product_id'] . ' not found')->error();
                            return back();
                        }

                        if ($product->type != Product::TYPE_PRODUCT_BUNDLE) {
                            flash('Error: Product with ID #' . $purchaseOrderProduct['product_id'] . ' is not a product bundle')->error();
                            return back();
                        }

                        if ($product->status != Product::STATUS_ACTIVE) {
                            flash('Error: Product with ID #' . $purchaseOrderProduct['product_id'] . ' is not available')->error();
                            return back();
                        }

                        PurchaseOrderItem::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'product_id' => $product->id,
                            'quantity' => $purchaseOrderProduct['quantity'],
                            'unit_price' => $product->cost_price,
                            'grand_total' => $purchaseOrderProduct['quantity'] * $product->cost_price,
                        ]);
                        $subtotal += $purchaseOrderProduct['quantity'] * $product->selling_price;
                        $totalCost += $purchaseOrderProduct['quantity'] * $product->cost_price;
                    }
                }
            }

            $purchaseOrder->tax_amount = $subtotal * ($input['tax_rate'] ?? 0) / 100;
            $purchaseOrder->tax_rate = $input['tax_rate'];
            $purchaseOrder->sub_total = $subtotal;
            $purchaseOrder->grand_total = $subtotal - $purchaseOrder->discount + $purchaseOrder->tax_amount;
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

            $subtotal = 0;
            $totalCost = 0;

            if (!empty($input['products'])) {
                $purchaseOrderItemIds = [];
                foreach ($input['products'] as $purchaseOrderProduct) {
                    if (!empty($purchaseOrderProduct['product_variant_id'])) {
                        $productVariant = ProductVariant::find($purchaseOrderProduct['product_variant_id']);
                        $product = $productVariant->product;

                        if (empty($productVariant)) {
                            flash('Error: Product variant with ID #' . $purchaseOrderProduct['product_variant_id'] . ' not found')->error();
                            return back();
                        }

                        if ($productVariant->status != ProductVariant::STATUS_ACTIVE || $productVariant->product->status != ProductVariant::STATUS_ACTIVE) {
                            flash('Error: Product variant with ID #' . $purchaseOrderProduct['product_variant_id'] . ' is not available')->error();
                            return back();
                        }

                        $purchaseOrderItem = $purchaseOrder->purchaseOrderItems()->where('purchase_order_items.id', $purchaseOrderProduct['product_variant_id'])->first();

                        if (!empty($purchaseOrderItem)) {
                            $purchaseOrderItem->update([
                                'quantity' => $product['quantity'],
                                'unit_price' => $productVariant->cost_price,
                                'grand_total' => $product['quantity'] * $productVariant->cost_price,
                            ]);
                        } else {
                            $purchaseOrderItem = PurchaseOrderItem::create([
                                'purchase_order_id' => $purchaseOrder->id,
                                'product_id' => $product->id,
                                'product_variant_id' => $productVariant->id,
                                'quantity' => $product['quantity'],
                                'unit_price' => $productVariant->cost_price,
                                'grand_total' => $product['quantity'] * $productVariant->cost_price,
                            ]);
                        }
                        $subtotal += $purchaseOrderProduct['quantity'] * $productVariant->selling_price;
                        $totalCost += $purchaseOrderProduct['quantity'] * $productVariant->cost_price;
                        $purchaseOrderItemIds[] = $purchaseOrderItem->id;
                    } else {
                        $product = Product::find($purchaseOrderProduct['product_id']);

                        if (empty($product)) {
                            flash('Error: Product with ID #' . $purchaseOrderProduct['product_id'] . ' not found')->error();
                            return back();
                        }

                        if ($product->type != Product::TYPE_PRODUCT_BUNDLE) {
                            flash('Error: Product with ID #' . $purchaseOrderProduct['product_id'] . ' is not a product bundle')->error();
                            return back();
                        }

                        if ($product->status != Product::STATUS_ACTIVE) {
                            flash('Error: Product with ID #' . $purchaseOrderProduct['product_id'] . ' is not available')->error();
                            return back();
                        }

                        $purchaseOrderItem = $purchaseOrder->purchaseOrderItems()->where('purchase_order_items.id', $purchaseOrderProduct['product_id'])->first();

                        if (!empty($purchaseOrderItem)) {
                            $purchaseOrderItem->update([
                                'quantity' => $product['quantity'],
                                'unit_price' => $product->cost_price,
                                'grand_total' => $product['quantity'] * $product->cost_price,
                            ]);
                        } else {
                            $purchaseOrderItem = PurchaseOrderItem::create([
                                'purchase_order_id' => $purchaseOrder->id,
                                'product_id' => $product->id,
                                'quantity' => $product['quantity'],
                                'unit_price' => $product->cost_price,
                                'grand_total' => $product['quantity'] * $product->cost_price,
                            ]);
                        }
                        $subtotal += $purchaseOrderProduct['quantity'] * $product->selling_price;
                        $totalCost += $purchaseOrderProduct['quantity'] * $product->cost_price;
                        $purchaseOrderItemIds[] = $purchaseOrderItem->id;
                    }
                }
                $purchaseOrder->purchaseOrderItems()->whereNotIn('purchase_order_items.id', $purchaseOrderItemIds)->delete();
            }

            $purchaseOrder->tax_amount = $subtotal * ($input['tax_rate'] ?? 0) / 100;
            $purchaseOrder->tax_rate = $input['tax_rate'];
            $purchaseOrder->sub_total = $subtotal;
            $purchaseOrder->grand_total = $subtotal - $purchaseOrder->discount + $purchaseOrder->tax_amount;
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
        $this->authorize('sendEmail', $purchaseOrder);

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
}
