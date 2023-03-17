@extends('layouts.layout')

@section('content')
    <admin-delivery-order-form
        csrf-token="{{ csrf_token() }}"
        form-action="{{ empty($deliveryOrder) ? route('admin.delivery-orders.store') : route('admin.delivery-orders.update', ['delivery_order' => $deliveryOrder]) }}"
        @if (!empty($deliveryOrder)) :delivery-order="{{ $deliveryOrder->toJson() }}" @endif
        @if (!empty(old())) :old-input="{{ json_encode(old()) }}" @endif/>
@stop
