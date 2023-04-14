@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">SALES ORDER INFORMATION</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.sales-orders.index') }}">Sales Order</a>
                    </li>
                    <li class="breadcrumb-item active">Edit Sales Order</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            @if ($salesOrder->status == App\Models\SalesOrder::STATUS_OUTSTANDING)
                <form action="{{ route('admin.sales-orders.updateItemStatus', [$salesOrder->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <sales-order-item-form :sales-order="{{ $salesOrder->load('salesOrderItems.product', 'salesOrderItems.productVariant', 'client') }}"></sales-order-item-form>
                </form>
            @else
                <form action="{{ route('admin.sales-orders.generateOrder', [$salesOrder->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <sales-order-show-form :sales-order="{{ $salesOrder->load('salesOrderItems.product', 'salesOrderItems.productVariant', 'client') }}"></sales-order-show-form>
                </form>
            @endif
        </div>
        <div class="row mb-2">
            <div class="col-md-1">
                <a href="{{ route('admin.sales-orders.index') }}" class="col-12 col-md-2"><button class="btn btn-secondary">Back</button></a>
            </div>
            <div class="col-md-11 text-end">
                <form action="{{ route('admin.sales-orders.destroy', [$salesOrder->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the sales order?')">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
