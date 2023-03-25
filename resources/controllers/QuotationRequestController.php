<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\QuotationRequest;
use App\Models\QuotationRequestItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;

class QuotationRequestController extends VeController
{
    public function store(Request $request)
    {
        $this->authorize('create', QuotationRequest::class);
        $input = $request->input();

        if ($input['status'] == QuotationRequest::STATUS_COMPLETED) {
            $this->authorize('create', PurchaseOrder::class);
        }

        $input['created_by'] = Auth::id();
        $validator = Validator::make($input, $this->model->createValidator);
        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $quotationRequest = QuotationRequest::create($input);
            if (!empty($input['products'])) {
                $quotationRequestItemIds = [];
                foreach ($input['products'] as $quotationProduct) {
                    if (!empty($quotationProduct['product_variant_id'])) {
                        $productVariant = ProductVariant::find($quotationProduct['product_variant_id']);
                        $product = $productVariant->product;

                        if (empty($productVariant)) {
                            flash('Error: Product variant with ID #' . $quotationProduct['product_variant_id'] . ' not found')->error();
                            return back();
                        }

                        if ($productVariant->status != ProductVariant::STATUS_ACTIVE || $productVariant->product->status != ProductVariant::STATUS_ACTIVE) {
                            flash('Error: Product variant with ID #' . $quotationProduct['product_variant_id'] . ' is not available')->error();
                            return back();
                        }

                        $quotationRequestItem = $quotationRequest->quotationRequestItems()->where('quotation_request_items.id', $quotationProduct['product_variant_id'])->first();

                        if (!empty($quotationRequestItem)) {
                            $quotationRequestItem->update([
                                'name' => $productVariant->product->name,
                                'sku' => $productVariant->product->sku,
                                'quantity' => $quotationProduct['quantity'],
                            ]);
                        } else {
                            $quotationRequestItem = QuotationRequestItem::create([
                                'quotation_request_id' => $quotationRequest->id,
                                'product_id' => $product->id,
                                'product_variant_id' => $productVariant->id,
                                'name' => $productVariant->product->name,
                                'sku' => $productVariant->product->sku,
                                'quantity' => $quotationProduct['quantity'],
                            ]);
                        }
                        $quotationRequestItemIds[] = $quotationRequestItem->id;
                    } else {
                        $product = Product::find($quotationProduct['product_id']);

                        if (empty($product)) {
                            flash('Error: Product with ID #' . $quotationProduct['product_id'] . ' not found')->error();
                            return back();
                        }

                        if ($product->type != Product::TYPE_PRODUCT_BUNDLE) {
                            flash('Error: Product with ID #' . $quotationProduct['product_id'] . ' is not a product bundle')->error();
                            return back();
                        }

                        if ($product->status != Product::STATUS_ACTIVE) {
                            flash('Error: Product with ID #' . $quotationProduct['product_id'] . ' is not available')->error();
                            return back();
                        }

                        $quotationRequestItem = $quotationRequest->quotationRequestItems()->where('quotation_request_items.id', $quotationProduct['product_id'])->first();

                        if (!empty($quotationRequestItem)) {
                            $quotationRequestItem->update([
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'quantity' => $quotationProduct['quantity'],
                            ]);
                        } else {
                            $quotationRequestItem = QuotationRequestItem::create([
                                'quotation_request_id' => $quotationRequest->id,
                                'product_id' => $product->id,
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'quantity' => $quotationProduct['quantity'],
                            ]);
                        }
                        $quotationRequestItemIds[] = $quotationRequestItem->id;
                    }
                }
                $quotationRequest->quotationRequestItems()->whereNotIn('quotation_request_items.id', $quotationRequestItemIds)->delete();
            }

            if ($input['status'] == QuotationRequest::STATUS_COMPLETED) {
                if (empty($input['products'])) {
                    flash('Please select at least one product.')->error();
                    return back();
                }

                $quotationRequest->createOrUpdatePurchaseOrder($input['products']);

                flash()->success('Successfully created the purchase order.');
                return redirect()->route('admin.purchase-orders.index');
            }

            DB::commit();
            flash()->success('Successfully created the quotation request.');
            return redirect()->route('admin.quotation-requests.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception->getMessage());
            return redirect()->route('admin.quotation-requests.create')->withInput($request->input());
        }
    }

    public function update(Request $request, $id)
    {
        $quotationRequest = $this->findModel($id);
        $this->authorize('update', $quotationRequest);
        $input = $request->input();

        if ($input['status'] == QuotationRequest::STATUS_COMPLETED) {
            $this->authorize('create', PurchaseOrder::class);
        }

        $input['created_by'] = Auth::id();

        $validator = Validator::make($input, $this->model->updateValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $quotationRequest->update($input);

            if (!empty($input['products'])) {
                $quotationRequestItemIds = [];
                foreach ($input['products'] as $quotationProduct) {
                    if (!empty($quotationProduct['product_variant_id'])) {
                        $productVariant = ProductVariant::find($quotationProduct['product_variant_id']);
                        $product = $productVariant->product;

                        if (empty($productVariant)) {
                            flash('Error: Product variant with ID #' . $quotationProduct['product_variant_id'] . ' not found')->error();
                            return back();
                        }

                        if ($productVariant->status != ProductVariant::STATUS_ACTIVE || $productVariant->product->status != ProductVariant::STATUS_ACTIVE) {
                            flash('Error: Product variant with ID #' . $quotationProduct['product_variant_id'] . ' is not available')->error();
                            return back();
                        }

                        $quotationRequestItem = $quotationRequest->quotationRequestItems()->where('quotation_request_items.id', $quotationProduct['product_variant_id'])->first();

                        if (!empty($quotationRequestItem)) {
                            $quotationRequestItem->update([
                                'name' => $productVariant->product->name,
                                'sku' => $productVariant->product->sku,
                                'quantity' => $quotationProduct['quantity'],
                            ]);
                        } else {
                            $quotationRequestItem = QuotationRequestItem::create([
                                'quotation_request_id' => $quotationRequest->id,
                                'product_id' => $product->id,
                                'product_variant_id' => $productVariant->id,
                                'name' => $productVariant->product->name,
                                'sku' => $productVariant->product->sku,
                                'quantity' => $quotationProduct['quantity'],
                            ]);
                        }
                        $quotationRequestItemIds[] = $quotationRequestItem->id;
                    } else {
                        $product = Product::find($quotationProduct['product_id']);

                        if (empty($product)) {
                            flash('Error: Product with ID #' . $quotationProduct['product_id'] . ' not found')->error();
                            return back();
                        }

                        if ($product->type != Product::TYPE_PRODUCT_BUNDLE) {
                            flash('Error: Product with ID #' . $quotationProduct['product_id'] . ' is not a product bundle')->error();
                            return back();
                        }

                        if ($product->status != Product::STATUS_ACTIVE) {
                            flash('Error: Product with ID #' . $quotationProduct['product_id'] . ' is not available')->error();
                            return back();
                        }

                        $quotationRequestItem = $quotationRequest->quotationRequestItems()->where('quotation_request_items.id', $quotationProduct['product_id'])->first();

                        if (!empty($quotationRequestItem)) {
                            $quotationRequestItem->update([
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'quantity' => $quotationProduct['quantity'],
                            ]);
                        } else {
                            $quotationRequestItem = QuotationRequestItem::create([
                                'quotation_request_id' => $quotationRequest->id,
                                'product_id' => $product->id,
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'quantity' => $quotationProduct['quantity'],
                            ]);
                        }
                        $quotationRequestItemIds[] = $quotationRequestItem->id;
                    }
                }
                $quotationRequest->quotationRequestItems()->whereNotIn('quotation_request_items.id', $quotationRequestItemIds)->delete();
            }

            if ($input['status'] == QuotationRequest::STATUS_COMPLETED) {
                if (empty($input['products'])) {
                    flash('Please select at least one product.')->error();
                    return back();
                }

                $quotationRequest->createOrUpdatePurchaseOrder($input['products']);

                flash()->success('Successfully created the purchase order.');
                return redirect()->route('admin.purchase-orders.index');
            }

            DB::commit();
            flash()->success('Successfully updated the quotation request.');
            return redirect()->route('admin.quotation-requests.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception->getMessage());
            return redirect()->route('admin.quotation-requests.edit', $quotationRequest->getRouteKey())->withInput($request->input());
        }
    }

    public function send(Request $request, QuotationRequest $quotationRequest)
    {
        $this->authorize('sendEmail', $quotationRequest);
        try {
            $data["email"] = $request->input('to_email');
            $data["title"] = 'Quotation Request' . ' ' . $quotationRequest->id;
            $data["quotationRequest"] = $quotationRequest;
            Mail::send('admin.quotation-requests.message', $data, function ($message) use ($data, $quotationRequest) {
                $message->to($data["email"], $data["email"])
                        ->subject($data["title"])
                        ->attach(Storage::url($quotationRequest->file_url));
            });
            $quotationRequest->status = QuotationRequest::STATUS_COMPLETED;
            $quotationRequest->save();
            flash()->success('Mail sent successfully!');
            return redirect()->route('admin.quotation-requests.index');
        } catch(Exception $exception) {
            Log::error('There was an issue sending the sending the pdf. Quotation Request ID: ' . $quotationRequest->id . ' . Error: ' . $exception->getMessage());
            flash()->error('There was an issue sending the sending the pdf. Quotation Request ID: ' . $quotationRequest->id . ' . Error: ' . $exception->getMessage());
            return redirect()->route('admin.quotation-requests.index');
        }
    }
}
