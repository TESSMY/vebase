<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;

class QuotationController extends VeController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Quotation::class);
        $input = $request->input();

        $validator = Validator::make($input, $this->model->createValidator);
        if ($validator->fails()) {
         flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
         return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $quotation = Quotation::create($input + ['created_by' => Auth::id()]);
            $subTotal = 0;

            if (!empty($input['products'])) {
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

                        $totalPrice = $productVariant->selling_price * $product['quantity'];
                        $quotationItem = new QuotationItem([
                            'quotation_id' => $quotation->id,
                            'product_id' => $productVariant->product_id,
                            'product_variant_id' => $productVariant->id,
                            'name' => $productVariant->name,
                            'sku' => $productVariant->sku,
                            'quantity' => $product['quantity'],
                            'cost_price' => $productVariant->product->cost_price,
                            'unit_price' => $productVariant->selling_price,
                            'total_price' => $totalPrice,
                        ]);
                        $quotationItem->save();
                        $subTotal += $totalPrice;
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

                        $totalPrice = $productModel->selling_price * $product['quantity'];
                        $quotationItem = new QuotationItem([
                            'quotation_id' => $quotation->id,
                            'product_id' => $productModel->id,
                            'name' => $productModel->name,
                            'sku' => $productModel->sku,
                            'quantity' => $product['quantity'],
                            'cost_price' => $productModel->cost_price,
                            'unit_price' => $productModel->selling_price,
                            'total_price' => $totalPrice,
                        ]);
                        $quotationItem->save();
                        $subTotal += $totalPrice;
                    }
                }
            }
            $quotation->total_items = count($input['products']);
            $quotation->sub_total = $subTotal;
            $grandTotal = $quotation->sub_total;
            if (!empty($input['tax_rate'])) {
                $tax = $subTotal * ($input['tax_rate'] / 100);
                $grandTotal += $tax;
                $quotation->tax_amount = $tax;
            }
            $quotation->grand_total = $grandTotal;
            $quotation->save();
            $quotation->generatePdf();

            if ($input['status'] == Quotation::STATUS_APPROVED) {
                $quotation->createOrUpdateSalesOrder($input['products']);
                DB::commit();
                flash()->success('Successfully created the sales order.');
                return redirect()->route('admin.sales-orders.index');
            }

            DB::commit();
            flash()->success('Successfully created the quotation.');
            return redirect()->route('admin.quotations.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception->getMessage());
            return redirect()->route('admin.quotations.create')->withInput($request->input());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $quotation = $this->findModel($id);
        $this->authorize('update', $quotation);
        $input = $request->input();
        $validator = Validator::make($input, $quotation->updateValidator);
        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }
        if ($quotation->salesOrder->status == SalesOrder::STATUS_COMPLETED) {
            flash('Error: Unable to edit due to sales order being completed.');
            return back();
        }
        try {
            DB::beginTransaction();

            $client = Client::find($input['client_id']);
            $quotation->update($input + ['client_id' => $client->id]);
            $subTotal = 0;
            $quotationItemIds = [];
            foreach ($input['products'] as $product) {
                if (!empty($product['quotation_item_id'])) {
                    // existing invoice item
                    $productModel = Product::find($product['product_id']);
                    $quotationItem = $quotation->quotationItem()->find($product['quotation_item_id']);
                    $quotationItem->update($product + [
                            'product_variant_id' => !empty($product['product_variant_id']) ? $product['quotation_item_id'] : null,
                            'name' => $productModel->name,
                        ]);
                    $quotationItemIds[] = $quotationItem->id;
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

                        $quotationItem = QuotationItem::create([
                            'quotation_id' => $quotation->id,
                            'product_id' => $productVariant->product_id,
                            'product_variant_id' => $productVariant->id,
                            'name' => $productVariant->name,
                            'sku' => $productVariant->sku,
                            'quantity' => $product['quantity'],
                            'unit_price' => $productVariant->selling_price,
                            'total_price' => $productVariant->selling_price * $product['quantity'],
                        ]);
                        $quotationItemIds[] = $quotationItem->id;
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

                        $quotationItem = QuotationItem::create([
                            'quotation_id' => $quotation->id,
                            'product_id' => $productModel->id,
                            'name' => $productModel->name,
                            'sku' => $productModel->sku,
                            'quantity' => $product['quantity'],
                            'unit_price' => $productModel->cost_price,
                            'total_price' => $productModel->cost_price * $product['quantity'],
                        ]);
                        $quotationItemIds[] = $quotationItem->id;
                    }
                }
                $subTotal += $quotationItem->total_price;
            }
            $quotation->quotationItems()->whereNotIn('quotation_items.id', $quotationItemIds)->delete();
            $quotation->total_items = count($input['products']);
            $quotation->sub_total = $subTotal;
            $grandTotal = $quotation->sub_total;
            if (!empty($input['tax_rate'])) {
                $tax = $subTotal * ($input['tax_rate'] / 100);
                $grandTotal += $tax;
                $quotation->tax_amount = $tax;
            }
            $quotation->grand_total = $grandTotal;
            $quotation->save();
            $quotation->generatePDF();
            $quotation->createOrUpdateSalesOrder($input['products']);
            DB::commit();
            flash()->success('Successfully updated quotation');
            return redirect()->route('admin.quotations.index');
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            flash()->error('There was an issue updating the quotation');
            return back()->withInput();
        }
    }

    public function send(Request $request, Quotation $quotation)
    {
        $this->authorize('create', $quotation);
        try {
            $data["email"] = $request->input('to_email');
            $data["title"] = 'Quotation' . ' ' . $quotation->id;
            $data["quotationRequest"] = $quotation;
            Mail::send('admin.quotations.message', $data, function ($message) use ($data, $quotation) {
                $message->to($data["email"])
                    ->subject($data["title"])
                    ->attach($quotation->file_url);
            });
            $quotation->status = Quotation::STATUS_SENT;
            $quotation->save();
            flash()->success('Mail sent successfully!');
            return redirect()->route('admin.quotations.index');
        } catch(Exception $exception) {
            Log::error('There was an issue sending the sending the pdf. Quotation Request ID: ' . $quotation->id . ' . Error: ' . $exception->getMessage());
            flash()->error('There was an issue sending the sending the pdf. Quotation Request ID: ' . $quotation->id . ' . Error: ' . $exception->getMessage());
            return redirect()->route('admin.quotations.index');
        }
    }
}
