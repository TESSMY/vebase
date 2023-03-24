@extends('layouts.layout')

@section('content')
    <admin-delivery-order-index :delivery-orders="{{ $deliveryOrders->toJson() }}"/>
@stop