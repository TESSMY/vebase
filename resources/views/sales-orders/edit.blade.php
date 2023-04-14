@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Edit Sales Order</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.sales-orders.edit', [$salesOrder->getRouteKey()]) }}">Sales Order</a>
                    </li>
                    <li class="breadcrumb-item active">Edit Sales Order</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <form action="{{ route('admin.sales-orders.update', [$salesOrder->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <sales-order-form :tax_rate="7" :sales-order="{{ $salesOrder->load('salesOrderItems.product', 'salesOrderItems.productVariant', 'client') }}"></sales-order-form>
            </form>
        </div>
        <div class="row mb-2">
            <div class="col-md-1">
                <a href="{{ route('admin.sales-orders.index') }}" class="col-12 col-md-2"><button class="btn btn-secondary">Cancel</button></a>
            </div>
            <div class="col-md-11 text-end">
                <form action="{{ route('admin.sales-orders.destroy', [$salesOrder->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
