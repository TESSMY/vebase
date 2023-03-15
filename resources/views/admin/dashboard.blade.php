@extends('layouts/layout')

@section('content')
    <dashboard
        :monthly-orders="{{ json_encode($monthlyOrders) }}"
        :purchase-orders="{{ json_encode($purchaseOrders) }}"
        :sales-orders="{{ json_encode($salesOrders) }}"/>
@endsection