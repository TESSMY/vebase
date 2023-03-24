<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
                foreach ($input['products'] as $quotationProduct) {
                    $productVariant = ProductVariant::find($quotationProduct['product_variant_id']);
                    $product = $productVariant->product;
                    QuotationRequestItem::create([
                        'quotation_request_id' => $quotationRequest->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $productVariant->id,
                        'name' => $productVariant->product->name,
                        'sku' => $productVariant->product->sku,
                        'quantity' => $quotationProduct['quantity'],
                    ]);
                }
            }

            $quotationRequest->file_url = $quotationRequest->generatePdf();
            $quotationRequest->save();

            if ($input['status'] == QuotationRequest::STATUS_COMPLETED) {
                $this->authorize('create', PurchaseOrder::class);

                $purchaseOrder = PurchaseOrder::create($input);

                if (!empty($input['products'])) {
                    foreach ($input['products'] as $purchaseOrderProduct) {
                        $productVariant = ProductVariant::find($purchaseOrderProduct['product_variant_id']);
                        $product = $productVariant->product;
                        PurchaseOrderItem::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $productVariant->id,
                            'quantity' => $purchaseOrderProduct['quantity'],
                            'unit_price' => $productVariant->cost_price,
                            'grand_total' => $purchaseOrderProduct['quantity'] * $productVariant->cost_price,
                        ]);
                    }
                }

                $purchaseOrder->file_url = $purchaseOrder->generatePdf();
                $purchaseOrder->save();

                DB::commit();
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

    public function edit(Request $request, $id)
    {
        $quotationRequest = $this->findModel($id);
        $this->authorize('update', $quotationRequest);
        $taxRate = 7;
        return view('admin.quotation-requests.edit', compact('taxRate', 'quotationRequest'));
    }

    public function update(Request $request, $id)
    {
        $quotationRequest = $this->findModel($id);
        $this->authorize('update', $quotationRequest);
        $input = $request->input();
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
                $quotationRequest->items->delete();
                foreach ($input['products'] as $quotationProduct) {
                    $productVariant = ProductVariant::find($quotationProduct['product_variant_id']);
                    $product = $productVariant->product;
                    QuotationRequestItem::create([
                        'quotation_request_id' => $quotationRequest->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $productVariant->id,
                        'name' => $productVariant->product->name,
                        'sku' => $productVariant->product->sku,
                        'quantity' => $quotationProduct['quantity'],
                    ]);
                }
                $quotationRequest->items->save();
            }

            $quotationRequest->file_url = $quotationRequest->generatePdf();
            $quotationRequest->save();

            if ($input['status'] == QuotationRequest::STATUS_COMPLETED) {
                $this->authorize('create', PurchaseOrder::class);

                if (!empty($existingPurchaseOrder = $quotationRequest->purchaseOrder)) {
                    if (!empty($existingPurchaseOrder->purchaseItems)) {
                        foreach($existingPurchaseOrder->purchaseItems as $purchaseItems) {
                            $purchaseItems->delete();
                        }
                    }
                    $existingPurchaseOrder->delete();
                }

                $purchaseOrder = PurchaseOrder::create($input);

                if (!empty($input['products'])) {
                    foreach ($input['products'] as $purchaseOrderProduct) {
                        $productVariant = ProductVariant::find($purchaseOrderProduct['product_variant_id']);
                        $product = $productVariant->product;
                        PurchaseOrderItem::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $productVariant->id,
                            'quantity' => $purchaseOrderProduct['quantity'],
                            'unit_price' => $productVariant->cost_price,
                            'grand_total' => $purchaseOrderProduct['quantity'] * $productVariant->cost_price,
                        ]);
                    }
                }

                $purchaseOrder->file_url = $purchaseOrder->generatePdf();
                $purchaseOrder->save();

                DB::commit();
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
