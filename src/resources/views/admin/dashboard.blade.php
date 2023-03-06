@extends('layouts/layout')

@section('content')
    <dashboard
        :monthly-orders="{{ json_encode($monthlyOrders) }}"
        :shipment-statics="{{ json_encode($shipmentStatics) }}"
        :purchase-order-statics="{{ json_encode($purchaseOrderStatics) }}"
        :revenue-statics="{{ json_encode($revenueStatics) }}"
        :sales-order-statics="{{ json_encode($salesOrderStatics) }}"
        :purchase-orders="{{ json_encode($purchaseOrders) }}"
        :sales-orders="{{ json_encode($salesOrders) }}">
        </dashboard>
@endsection