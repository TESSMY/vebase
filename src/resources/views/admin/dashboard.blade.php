@extends('layouts/layout')

@section('content')
    <dashboard
        :monthly-orders="{{ json_encode($monthlyOrders) }}"
        :shipment-statistics="{{ json_encode($shipmentStatistics) }}"
        :purchase-order-statistics="{{ json_encode($purchaseOrderStatistics) }}"
        :revenue-statistics="{{ json_encode($revenueStatics) }}"
        :sales-order-statistics="{{ json_encode($salesOrderStatistics) }}"
        :purchase-orders="{{ json_encode($purchaseOrders) }}"
        :sales-orders="{{ json_encode($salesOrders) }}"/>
@endsection