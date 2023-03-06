<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit') ?? 10;
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');

        $invoices = Invoice::query();

        if (!empty($search)) {
            if (!empty(Invoice::class()->searchable)) {
                $invoices = $invoices->where(function($query) use ($search) {
                    foreach (Invoice::class()->searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%' . $search . '%');
                    }
                });
            }
        }

        if (!empty($orderColumn) && in_array($orderColumn, Invoice::class()->sortable)) {
            $invoices = $invoices->orderBy($orderColumn, $orderBy);
        }

        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'oldest'){
            $invoices->oldest();
        } elseif ($sortBy === 'latest'){
            $invoices->latest();
        }

        $invoices = $invoices->paginate($limit)->withQueryString();
        
        $compact = [
            'models' => $invoices,
        ];
        
        return view('admin.invoices.index', $compact);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.invoices.create');
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
    public function show(Invoice $invoice)
    {
        return view('admin.invoices.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit(Invoice $invoice)
    {
        return view('admin.invoices.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice)
    {
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        flash()->success('Successfully deleted invoice');
        return redirect()->route('admin.invoices.index');
    }
}
