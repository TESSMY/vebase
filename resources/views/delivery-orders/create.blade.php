@extends('layouts.layout')

@section('content')
    <admin-delivery-order-form
        csrf-token="{{ csrf_token() }}"
        :tax-rate="{{ $taxRate->toJson() }}"
        :taxes="{{ $taxes->toJson() }}"
        form-action="{{ route('admin.delivery-orders.store') }}"
        @if (!empty(old())) :old-input="{{ json_encode(old()) }}" @endif/>
@stop
