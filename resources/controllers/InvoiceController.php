<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;
use Illuminate\Support\Str;

class InvoiceController extends VeController
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $clients = Client::get();
        $salesOrder = SalesOrder::get();
        $products = Product::where('type', Product::TYPE_PRODUCT_BUNDLE)->get();
        $variants = ProductVariant::get();
        $collection = collect();
        foreach ($products as $key => $product) {
            $collection->push($product);
        }
        foreach ($variants as $key => $variant) {
            $collection->push($variant);
        }

        $compact = [
            'clients' => $clients,
            'salesOrders' => $salesOrder,
            'products' => $collection,
            'taxRate' => 7,
            'routeModel' => Str::singular($this->routeName),
            'model' => $this->model,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
        ];

        return view('admin.invoices.create', $compact);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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

            $invoice = Invoice::create($input + ['created_by' => Auth::id()]);

            foreach ($input['products'] as $product) {
                if (isset($product['product_variant_id'])) {
                    $productVariant = ProductVariant::find($product['product_variant_id']);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $productVariant->product_id, 
                        'product_variant_id' => $productVariant->id,
                        'name' => $productVariant->name, 
                        'quantity' => $product['quantity'], 
                        'unit_price' => $productVariant->selling_price, 
                        'sub_total' => $product->selling_price * $product['quantity'], 
                    ]);
                } else {
                    $product = Product::find($product['id']);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $product->id, 
                        'name' => $product->name, 
                        'quantity' => $product['quantity'], 
                        'unit_price' => $product->selling_price, 
                        'sub_total' => $product->selling_price * 1, 
                    ]);
                }
                
            }

            DB::commit();
            flash()->success('Successfully created invoice');
            return redirect()->route('admin.invoices.index');
        } catch (Exception $exception) {
            log()::error($exception);
            DB::rollBack();
            flash()->error('There was an issue creating invoice');
            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $invoice)
    {
        return view('admin.invoices.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $invoice)
    {
        $salesOrder = SalesOrder::get(['id']);
        // $productBundles = Product::get(['id', 'name']);
        $productVariants = ProductVariant::get(['id', 'name']);

        $compact = [
            'salesOrders' => $salesOrder,
            // 'productBundles' => $productBundles,
            'productVariants' => $productVariants,
            'taxRate' => 7,
            'routeModel' => Str::singular($this->routeName),
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
        $input = $request->input();

        $validator = Validator::make($input, $invoice->createValidator());

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        try {
            DB::beginTransaction();



            DB::commit();
            flash()->success('Successfully updated invoice');
            return redirect()->route('admin.invoices.index');
        } catch (Exception $exception) {
            log()::error($exception);
            DB::rollBack();
            flash()->error('There was an issue updating invoice');
            return back()->withInput();
        }
    }
}
