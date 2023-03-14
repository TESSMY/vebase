@extends('layouts.layout')

@section('content')
    <admin-delivery-order-form
        action="{{ $action }}"
        form-action="{{ $action == 'create' ? route('admin.delivery-orders.store') : route('admin.delivery-orders.update', ['delivery_order' => $deliveryOrder]) }}"
        @if (!empty($deliveryOrder)) :delivery-order="{{ $deliveryOrder->toJson() }}" @endif/>
@stop
