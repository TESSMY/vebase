<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DeliveryOrderController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        app('debugbar')->disable();
        // $this->authorizeResource(DeliveryOrder::class, 'delivery_order');
    }

    public function index()
    {
        $deliveryOrders = DeliveryOrder::with('salesOrder')->paginate();
        return View::make('vebase::admin.delivery-orders.index', compact('deliveryOrders'));
    }

    public function create()
    {
        $action = 'create';
        return View::make('vebase::admin.delivery-orders.form', compact('action'));
    }

    public function store()
    {
        request()->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.quantity' => 'required|integer|min:1'
        ]);

        $deliveryOrder = DeliveryOrder::create(request()->only(['supplier_id', 'date', 'note']));
        return $deliveryOrder;
    }

    public function edit(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load('salesOrder');
        $action = 'edit';
        return View::make('vebase::admin.delivery-orders.form', compact('deliveryOrder', 'action'));
    }

    public function update(DeliveryOrder $deliveryOrder)
    {
        if ($deliveryOrder->status != DeliveryOrder::STATUS_PENDING) {
            return abort(400, __('Delivery order proceed already'));
        }

        request()->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.quantity' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        $deliveryOrder->update(request()->only(['supplier_id', 'date', 'note']));
        $remainItems = array_column(request('items'), 'id');

        // DELETE IF NOT EXISTS IN REQUEST
        $deliveryOrder->items()->whereNotIn('product_variant_id', $remainItems)->delete();
        $subTotal = 0;
        $productVariant = ProductVariant::select(['id', 'retail_price'])
            ->whereIn('id', $remainItems)
            ->get()
            ->pluck('retail_price', 'id');
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

    public function destroy(DeliveryOrder $deliveryOrder)
    {
        
    }

    public function listProduct()
    {
        request()->validate(['name' => 'required|string|min:1']);
        $products = Product::where('name', 'like', '%' . request('query') . '%')->limit(30)->get();
        return $products;
    }

    public function listSalesOrder()
    {
        request()->validate(['query' => 'required|string|min:1']);
        $salesOrders = SalesOrder::where(function ($q) {
                $q->where('created_at', 'like', '%' . request('query') . '%')
                    ->orWhere('id', 'like', '%' . request('query') . '%');
            })
            ->limit(30)
            ->get();
        return $salesOrders;
    }
}
