<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;
use Illuminate\Support\Str;

class InvoiceController extends VeController
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
            $invoice = Invoice::create($input + ['client_name' => $client->name, 'created_by' => Auth::id()]);
            $subTotal = 0;

            foreach ($input['products'] as $product) {

                if (isset($product['product_variant_id'])) {
                    
                    $productVariant = ProductVariant::find($product['product_variant_id']);
                    
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $productVariant->product_id, 
                        'product_variant_id' => $productVariant->id,
                        'name' => $productVariant->name, 
                        'sku' => $productVariant->sku, 
                        'quantity' => $product['quantity'], 
                        'unit_price' => $productVariant->selling_price, 
                        'total_price' => $productVariant->selling_price * $product['quantity'], 
                    ]);
                } else {
                    $productModel = Product::find($product['product_id']);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $productModel->id, 
                        'name' => $productModel->name, 
                        'sku' => $productModel->sku, 
                        'quantity' => $product['quantity'], 
                        'unit_price' => $productModel->cost_price, 
                        'total_price' => $productModel->cost_price * $product['quantity'], 
                    ]);
                }
                $subTotal += $invoiceItem->total_price; 
            }

            $invoice->item_count = count($input['products']);
            $invoice->sub_total = $invoice->invoiceItems->sum('total_price');
            $invoice->grand_total = $invoice->sub_total;
            $invoice->save();
            $invoice->generatePDF();

            DB::commit();
            flash()->success('Successfully created invoice');
            return redirect()->route('admin.invoices.index');
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            flash()->error('There was an issue creating invoice');
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $invoice)
    {
        $invoice = $this->findModel($invoice);
        $this->authorize('update', $invoice);
        $invoice->load('client', 'invoiceItems.product', 'invoiceItems.productVariant');

        $compact = [
            'invoice' => $invoice,
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
        ];

        return view('admin.invoices.edit', $compact);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $invoice)
    {
        $invoice = $this->findModel($invoice);

        $this->authorize('update', $invoice);

        $input = $request->input();

        if (empty($invoice->updateValidator())) {
            flash('Error: updateValidator is empty')->error();
            return back(); 
        }

        $validator = Validator::make($input, $invoice->updateValidator());

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        try {
            DB::beginTransaction();
            

            $client = Client::find($input['client_id']);
            $invoice->update($input + ['client_name' => $client->name, 'created_by' => Auth::id()]);
            $subTotal = 0;

            foreach ($input['products'] as $product) {
                if (!empty($product['invoice_item_id'])) {
                    // existing invoice item
                    $productModel = Product::find($product['product_id']);
                    $invoiceItem = InvoiceItem::find($product['invoice_item_id']);
                    $invoiceItem->update($product + [
                        'product_variant_id' => !empty($product['product_variant_id']) ? $product['invoice_item_id'] : null, 
                        'name' => $productModel->name,
                    ]);
                } else {
                    if (isset($product['product_variant_id'])) {
                        // product variant & single product
                        $productVariant = ProductVariant::find($product['product_variant_id']);
    
                        $invoiceItem = InvoiceItem::create([
                            'invoice_id' => $invoice->id,
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
    
                        $invoiceItem = InvoiceItem::create([
                            'invoice_id' => $invoice->id,
                            'product_id' => $productModel->id, 
                            'name' => $productModel->name,
                            'sku' => $productModel->sku,
                            'quantity' => $product['quantity'], 
                            'unit_price' => $productModel->cost_price, 
                            'total_price' => $productModel->cost_price * $product['quantity'], 
                        ]);
                    }
                }
                $subTotal += $invoiceItem->total_price;
            }

            $invoice->item_count = count($input['products']);
            $invoice->sub_total = $subTotal;
            $invoice->grand_total = $invoice->sub_total;
            $invoice->save();
            $invoice->generatePDF();

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
