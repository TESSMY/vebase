<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
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
        $clients = User::get(['id', 'name']);
        $salesOrder = SalesOrder::get(['id']);
        $products = Product::get(['id', 'name']);

        $compact = [
            'clients' => $clients,
            'salesOrders' => $salesOrder,
            'products' => $products,
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

        $validator = Validator::make($input, Invoice::class()->createValidator());

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        $client = User::find($input['user_id']);
        if (!empty($input['sales_order_id'])) {
            $salesOrder = SalesOrder::find($input['sales_order_id'])->with('orderItems');
        }
        
        try {
            DB::beginTransaction();

            $invoice = Invoice::create($input + ['created_by' => Auth::id()]);

            if (!empty($salesOrder) && $salesOrder->orderItems->isNotEmpty()) {
                foreach ($salesOrder->orderItems as $orderItem) {
                    $orderItem = InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $orderItem->product_id, 
                        'product_variant_id' => $orderItem->product_variant_id,
                        'name' => $orderItem->productVariant->name, 
                        'quantity' => $orderItem->quantity, 
                        'unit_price' => $orderItem->unit_price, 
                        'sub_total' => $orderItem->sub_total, 
                    ]);
                }
            } else {
                foreach ($input['products'] as $productVariant) {
                    $productVariant = ProductVariant::find($productVariant);
                    $orderItem = InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $productVariant->product_id, 
                        'product_variant_id' => $productVariant->id,
                        'name' => $productVariant->name, 
                        'quantity' => $orderItem->quantity, 
                        'unit_price' => $productVariant->unit_price, 
                        'sub_total' => $productVariant->unit_price * 1, 
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
