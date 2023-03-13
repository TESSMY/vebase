<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $purchaseOrders = PurchaseOrder::query();

        if (!empty($search)) {
            $purchaseOrders = $purchaseOrders->where(function ($query) use ($search) {
                $query->where('user_id', 'LIKE', '%' . $search . '%')
                    ->orWhere('certificate_id', 'LIKE', '%' . $search . '%');
            });
        }

        $purchaseOrders = $purchaseOrders->latest()->paginate(10)->withQueryString();

        return view('admin.purchase-orders.index', compact('purchaseOrders'));
    }

    public function create(Request $request)
    {
        $taxRate = 7;
        return view('admin.purchase-orders.create', compact('taxRate'));
    }

    public function store(Request $request)
    {
        $input = $request->input();
        $model = PurchaseOrder::first();
        $validator = Validator::make($input, $model->createValidator);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                flash('Error: ' . $error)->error();
            }
            return back()->withInput($request->input())->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::create($input);
            DB::commit();
            flash()->success('Successfully created the purchase order.');
            return redirect()->route('admin.purchase-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception);
            return redirect()->route('admin.purchase-orders.create')->withInput($request->input());
        }
    }

    public function edit(Request $request, PurchaseOrder $purchaseOrder)
    {
        $taxRate = 7;
        return view('admin.purchase-orders.edit', compact('taxRate', 'purchaseOrder'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $input = $request->input();
        $validator = Validator::make($input, $purchaseOrder->updateValidator);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                flash('Error: ' . $error)->error();
            }
            return back()->withInput($request->input())->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $purchaseOrder->update($input);
            DB::commit();
            flash()->success('Successfully updated the purchase order.');
            return redirect()->route('admin.purchase-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception);
            return redirect()->route('admin.purchase-orders.edit', $purchaseOrder->getRouteKey())->withInput($request->input());
        }
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder)
    {
        $purchase_order = $purchaseOrder;

        if (empty($purchaseOrder->file_url)) {
            $purchaseOrder->file_url = $purchaseOrder->generatePdf();
            $purchaseOrder->save();
        }

        return view('admin.purchase-orders.show', compact('purchaseOrder', 'purchase_order'));
    }

    public function send(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data["email"] = $request->input('to_email');
        $data["title"] = 'Purchase Order' . ' ' . $purchaseOrder->id;
        $data["purchaseOrder"] = $purchaseOrder;
        Mail::send('admin.purchase-orders.message', $data, function ($message) use ($data, $purchaseOrder) {
            $message->to($data["email"], $data["email"])
                    ->subject($data["title"])
                    ->attach(Storage::url($purchaseOrder->file_url));
        });

        flash()->success('Mail sent successfully!');
        return redirect()->route('admin.purchase-orders.index');
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->delete();
            flash()->success('Successfully deleted the purchase order.');
            return redirect()->route('admin.purchase-orders.index');
        } catch (Exception $exception) {
            Log::error($exception);
            flash('Error: ' . $exception);
            return redirect()->route('admin.purchase-orders.index');
        }
    }
}