<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Vecapital\Vebase\Http\Controllers\VeController;

class PurchaseOrderController extends VeController
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $search = $request->input('search');
        $orderColumn = $request->input('order_column');
        $orderBy = $request->input('order_by');
        $purchaseOrders = PurchaseOrder::query();

        if (!empty($search)) {
            if (!empty($this->model->searchable)) {
                $purchaseOrders = $purchaseOrders->where(function($query) use ($search) {
                    foreach ($this->model->searchable as $value) {
                        $query->orWhere($value, 'LIKE', '%' . $search . '%');
                    }
                });
            }
        }

        if (!empty($orderColumn) && in_array($orderColumn, $this->model->sortable)) {
            $purchaseOrders = $purchaseOrders->orderBy($orderColumn, $orderBy);
        }


        $purchaseOrders = $purchaseOrders->latest()->paginate(10)->withQueryString();

        return view('admin.purchase-orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $this->authorize('create', PurchaseOrder::class);
        $taxRate = 7;
        return view('admin.purchase-orders.create', compact('taxRate'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PurchaseOrder::class);
        $input = $request->input();
        $input['created_by'] = Auth::id();

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::create($input);

            if (!empty($input['products'])) {
                foreach ($input['products'] as $product) {
                    $productVariant = ProductVariant::find($product['product_variant_id']);
                    $product = $productVariant->product;
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $productVariant->id,
                        'quantity' => $product['quantity'],
                        'unit_price' => $productVariant->cost_price,
                        'grand_total' => $product['quantity'] * $productVariant->cost_price,
                    ]);
                }
            }

            $purchaseOrder->file_url = $purchaseOrder->generatePdf();
            $purchaseOrder->save();

            DB::commit();
            flash()->success('Successfully created the purchase order.');
            return redirect()->route('admin.purchase-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception->getMessage());
            return redirect()->route('admin.purchase-orders.create')->withInput($request->input());
        }
    }

    public function edit(Request $request, $id)
    {
        $purchaseOrder = $this->findModel($id);
        $this->authorize('update', $purchaseOrder);
        $taxRate = 7;
        return view('admin.purchase-orders.edit', compact('taxRate', 'purchaseOrder'));
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = $this->findModel($id);
        $this->authorize('update', $purchaseOrder);
        $input = $request->input();
        $input['created_by'] = Auth::id();

        $validator = Validator::make($input, $this->model->updateValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->update($input);

            if (!empty($input['products'])) {
                $purchaseOrder->purchaseItems->delete();
                foreach ($input['products'] as $product) {
                    $productVariant = ProductVariant::find($product['product_variant_id']);
                    $product = $productVariant->product;
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $productVariant->id,
                        'quantity' => $product['quantity'],
                        'unit_price' => $productVariant->cost_price,
                        'grand_total' => $product['quantity'] * $productVariant->cost_price,
                    ]);
                }
                $purchaseOrder->orderItems->save();
            }

            $purchaseOrder->file_url = $purchaseOrder->generatePdf();
            $purchaseOrder->save();

            DB::commit();
            flash()->success('Successfully updated the purchase order.');
            return redirect()->route('admin.purchase-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error: ' . $exception->getMessage());
            return redirect()->route('admin.purchase-orders.edit', $purchaseOrder->getRouteKey())->withInput($request->input());
        }
    }

    public function send(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('sendEmail', $purchaseOrder);

        try {
            $data["email"] = $request->input('to_email');
            $data["title"] = 'Purchase Order' . ' ' . $purchaseOrder->id;
            $data["purchaseOrder"] = $purchaseOrder;
            Mail::send('admin.purchase-orders.message', $data, function ($message) use ($data, $purchaseOrder) {
                $message->to($data["email"], $data["email"])
                        ->subject($data["title"])
                        ->attach(Storage::url($purchaseOrder->file_url));
            });

            $purchaseOrder->status = PurchaseOrder::STATUS_COMPLETED;
            $purchaseOrder->save();

            flash()->success('Mail sent successfully!');
            return redirect()->route('admin.purchase-orders.index');
        } catch(Exception $exception) {
            Log::error('There was an issue sending the sending the pdf. Purchase Order ID: ' . $purchaseOrder->id . ' . Error: ' . $exception->getMessage());
            flash()->error('There was an issue sending the sending the pdf. Purchase Order ID: ' . $purchaseOrder->id . ' . Error: ' . $exception->getMessage());
            return redirect()->route('admin.purchase-orders.index');
        }

    }
}
