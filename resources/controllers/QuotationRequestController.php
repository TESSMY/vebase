<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\QuotationRequest;
use App\Models\QuotationRequestItem;
use App\Models\Supplier;
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

        $supplier = Supplier::find($input['supplier_id']);

        if (empty($supplier)) {
            flash()->error('Could not find the supplier selected. Please select a different supplier.');
            return back()->withInput($request->input());
        }

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();

        try {
            $quotationRequest = QuotationRequest::create($input);

            $this->updateOrCreateItem($quotationRequest, $input['products']);

            if ($input['send_email']) {
                $this->send($request, $quotationRequest);
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

        $validator = Validator::make($input, $this->model->updateValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();

        try {
            $quotationRequest->update($input);

            $this->updateOrCreateItem($quotationRequest, $input['products']);

            if ($input['send_email']) {
                $this->send($request, $quotationRequest);
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
        $this->authorize('update', $quotationRequest);
        try {
            $data["email"] = $request->input('to_email');
            $data["title"] = 'Quotation Request' . ' ' . $quotationRequest->id;
            $data["quotationRequest"] = $quotationRequest;
            $filePath = $quotationRequest->generatePdf();
            $quotationRequest->file_url = $filePath;
            $quotationRequest->save();
            Mail::send('admin.quotation-requests.message', $data, function ($message) use ($data, $quotationRequest) {
                $message->to($data["email"], $data["email"])
                        ->subject($data["title"])
                        ->attach(Storage::url($quotationRequest->file_url . '.pdf'));
            });
            $quotationRequest->status = QuotationRequest::STATUS_PENDING;
            $quotationRequest->save();

            flash()->success('Mail sent successfully!');
            return true;
        } catch(Exception $exception) {
            Log::error('There was an issue sending the pdf. Quotation Request ID: ' . $quotationRequest->id . ' . Error: ' . $exception->getMessage());
            flash()->error('There was an issue sending the sending the pdf. Quotation Request ID: ' . $quotationRequest->id . ' . Error: ' . $exception->getMessage());
            return redirect()->route('admin.quotation-requests.index');
        }
    }

    public function generatePo(Request $request, QuotationRequest $quotationRequest)
    {
        $this->authorize('create', PurchaseOrder::class);

        if (empty($quotationRequest->quotationRequestItems)) {
            flash()->error('There are no products in this quotation request. Please add some products before generating the purchase order.');
            return redirect()->route('admin.quotation-requests.edit', $quotationRequest->getRouteKey());
        }

        $quotationRequest->createPurchaseOrder();
        $quotationRequest->status = QuotationRequest::STATUS_APPROVED;
        $quotationRequest->save();
        flash()->success('Successfully created the purchase order.');
        return redirect()->route('admin.purchase-orders.index');
    }

    public function updateOrCreateItem(QuotationRequest $quotationRequest, $selectedProducts)
    {
        if (empty($selectedProducts)) {
            flash()->error('Selected products is empty, please add a product in order to create quotation request items. Quotation Request ID: ' . $quotationRequest->id);
            return back();
        }

        try {
            $quotationRequestItemIds = [];
            foreach ($selectedProducts as $selectedProduct) {
                if (!empty($selectedProduct['quotation_request_item_id'])) {
                    // existing item
                    $quotationRequestItem = $quotationRequest->quotationRequestItems()->find($selectedProduct['quotation_request_item_id']);
                    $quotationRequestItem->update([
                            'quantity' => $selectedProduct['quantity'],
                    ]);
                    $quotationRequestItemIds[] = $quotationRequestItem->id;
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

                        $quotationRequestItem = QuotationRequestItem::create([
                            'quotation_request_id' => $quotationRequest->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $productVariant->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'description' => $product->description,
                            'quantity' => $selectedProduct['quantity'],
                        ]);

                        $quotationRequestItemIds[] = $quotationRequestItem->id;
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

                        $quotationRequestItem = QuotationRequestItem::create([
                            'quotation_request_id' => $quotationRequest->id,
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'description' => $product->description,
                            'quantity' => $selectedProduct['quantity'],
                        ]);

                        $quotationRequestItemIds[] = $quotationRequestItem->id;
                    }
                }
            }
            $quotationRequest->quotationRequestItems()->whereNotIn('quotation_request_items.id', $quotationRequestItemIds)->delete();
            $quotationRequest->save();

            return $quotationRequest;
        } catch (Exception $exception) {
            Log::error($exception);
            flash('There was an error creating the quotation request item. Quotation Request ID: '  . $quotationRequest->id . '. Error: ' . $exception->getMessage());
            return back();
        }
    }
}
