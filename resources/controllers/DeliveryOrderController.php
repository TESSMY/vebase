<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use App\Models\DeliveryOrder;
use App\Models\SalesOrder;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index()
    {
        $deliveryOrders = DeliveryOrder::with(['salesOrder', 'items.productVariant.product'])->paginate();
        return view('admin.delivery-orders.index', compact('deliveryOrders'));
    }

    public function create()
    {
        $action = 'create';
        return view('admin.delivery-orders.form', compact('action'));
    }

    public function store()
    {
        request()->validate([
            'cient_id' => 'required|exists:clients,id',
            'sales_order_id' => 'required|exists:sales_orders,id',
            'date' => 'required|date',
            'packed_by_date' => 'nullable|date',
            'payment_term' => 'nullable|string',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $deliveryOrder = DeliveryOrder::create(request()->only(['cient_id', 'sales_order_id', 'date', 'packed_by_date', 'payment_term', 'note']));
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
                    'sub_total' => $subTotalItem
                ]
            );
            $subTotal += $subTotalItem;
        }

        $deliveryOrder->grant_total = $subTotal;
        $deliveryOrder->save();

        return $deliveryOrder;
    }

    public function edit(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load(['salesOrder', 'items.productVariant.product']);
        $action = 'edit';
        return view('admin.delivery-orders.form', compact('deliveryOrder', 'action'));
    }

    public function update(DeliveryOrder $deliveryOrder)
    {
        if ($deliveryOrder->status != DeliveryOrder::STATUS_PENDING) {
            return abort(400, __('Delivery order proceed already'));
        }

        request()->validate([
            'cient_id' => 'required|exists:clients,id',
            'sales_order_id' => 'required|exists:sales_orders,id',
            'date' => 'required|date',
            'packed_by_date' => 'nullable|date',
            'payment_term' => 'nullable|string',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        $deliveryOrder->update(request()->only(['cient_id', 'sales_order_id', 'date', 'packed_by_date', 'payment_term', 'note']));
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
                    'sub_total' => $subTotalItem
                ]
            );
            $subTotal += $subTotalItem;
        }

        $deliveryOrder->sub_total = $subTotal;
        $deliveryOrder->save();

        DB::commit();
        return $deliveryOrder;
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
                ->orWhere('grant_total', 'like', '%' . request('query') . '%');
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
