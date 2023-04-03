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

            $subTotal = $deliveryOrder->createDeliveryOrderItems($input['products']);

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

            $subTotal = $deliveryOrder->createDeliveryOrderItems($input['products']);

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
