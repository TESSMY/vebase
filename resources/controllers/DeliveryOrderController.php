<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\DeliveryOrder;
use App\Models\SalesOrder;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;

class DeliveryOrderController extends VeController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', DeliveryOrder::class);
        $deliveryOrders = DeliveryOrder::with(['salesOrder', 'items.productVariant.product'])->paginate();
        return view('admin.delivery-orders.index', compact('deliveryOrders'));
    }

    public function create()
    {
        $this->authorize('create', DeliveryOrder::class);
        $action = 'create';
        return view('admin.delivery-orders.form', compact('action'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', DeliveryOrder::class);
        $input = $request->input();
        $input['user_id'] = auth()->id();
        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash()->error(implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $deliveryOrder = DeliveryOrder::create($input);
            $subTotal = 0;
            $productVariant = ProductVariant::select(['id', 'selling_price'])
                ->whereIn('id', array_column(request('items'), 'id'))
                ->get()
                ->pluck('selling_price', 'id');
            foreach (request('items') as $itemRequest) {
                $subTotalItem = $itemRequest['quantity'] * $productVariant[$itemRequest['id']];
                $deliveryOrder->items()->updateOrCreate(
                    ['product_variant_id' => $itemRequest['id']],
                    [
                        'quantity' => $itemRequest['quantity'],
                        'sub_total' => $subTotalItem,
                        'unit_price' => $productVariant[$itemRequest['id']]
                    ]
                );
                $subTotal += $subTotalItem;
            }

            $deliveryOrder->grand_total = $subTotal;
            $deliveryOrder->save();
            DB::commit();

            flash()->success(__('Successfully created the delivery order.'));
            return redirect()->route('admin.delivery-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error($exception->getMessage());
            return redirect()->route('admin.delivery-orders.create')->withInput($request->input());
        }
    }

    public function edit(Request $request, $id)
    {
        $deliveryOrder = $this->findModel($id);
        $this->authorize('update', $deliveryOrder);
        $deliveryOrder->load(['salesOrder', 'items.productVariant.product']);
        $action = 'edit';
        return view('admin.delivery-orders.form', compact('deliveryOrder', 'action'));
    }

    public function update(Request $request, $id)
    {
        $deliveryOrder = $this->findModel($id);
        $this->authorize('update', $deliveryOrder);

        $input = $request->input();
        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $deliveryOrder->update($input);
            $remainItems = array_column(request('items'), 'id');

            // DELETE IF NOT EXISTS IN REQUEST
            $deliveryOrder->items()->whereNotIn('product_variant_id', $remainItems)->delete();
            $subTotal = 0;
            $productVariant = ProductVariant::select(['id', 'selling_price'])
                ->whereIn('id', $remainItems)
                ->get()
                ->pluck('selling_price', 'id');
            foreach (request('items') as $itemRequest) {
                $subTotalItem = $itemRequest['quantity'] * $productVariant[$itemRequest['id']];
                $deliveryOrder->items()->updateOrCreate(
                    ['product_variant_id' => $itemRequest['id']],
                    [
                        'quantity' => $itemRequest['quantity'],
                        'sub_total' => $subTotalItem,
                        'unit_price' => $productVariant[$itemRequest['id']]
                    ]
                );
                $subTotal += $subTotalItem;
            }

            $deliveryOrder->sub_total = $subTotal;
            $deliveryOrder->save();
            DB::commit();

            flash()->success(__('Successfully updated the delivery order.'));
            return redirect()->route('admin.delivery-orders.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash()->error($exception->getMessage());
            return redirect()->route('admin.delivery-orders.edit', $deliveryOrder->getRouteKey())->withInput($request->input());
        }
    }

    public function listProduct()
    {
        $items = ProductVariant::with('product');
        if (!empty(request())) {
            $items->where('product_variants.name', 'like', '%' . request('query') . '%')
                ->orWhere('product_variants.sku', 'like', '%' . request('query') . '%')
                ->orWhereHas('product', function ($product) {
                    $product->where('products.name', 'like', '%' . request('query') . '%')->orWhere('products.sku', 'like', '%' . request('query') . '%');
                });
        }
        $items = $items->limit(30)->get();
        return $items;
    }

    public function listSalesOrder()
    {
        $salesOrders = SalesOrder::with(['user' => function ($user) { return $user->select('name', 'id'); }]);

        if (!empty(request('query'))) {
            $salesOrders->where(function ($q) {
                $q->where('created_at', 'like', '%' . request('query') . '%')
                ->orWhere('id', 'like', '%' . request('query') . '%')
                ->orWhereHas('user', function ($user) {
                    $user->where('name', 'like', '%' . request('query') . '%');
                })
                ->orWhere('grand_total', 'like', '%' . request('query') . '%');
            });
        }

        $salesOrders = $salesOrders->where('status', '<>', SalesOrder::STATUS_SHIPPED)
        ->orderBy('created_at', 'desc')
        ->get();
        return $salesOrders;
    }

    public function sendEmail(DeliveryOrder $deliveryOrder)
    {

    }

    public function downloadPdf(DeliveryOrder $deliveryOrder)
    {

    }
}
