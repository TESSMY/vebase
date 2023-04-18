@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Edit Purchase Order</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                  <li class="breadcrumb-item"><a href="{{ route('admin.purchase-orders.edit', [$purchaseOrder->getRouteKey()]) }}">Purchase Order</a></li>
                  <li class="breadcrumb-item active">Edit Purchase Order</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <form action="{{ route('admin.purchase-orders.update', [$purchaseOrder->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <purchase-order-form :purchase-order="{{ $purchaseOrder->load('purchaseOrderItems.product', 'purchaseOrderItems.productVariant', 'supplier', 'client') }}"></purchase-order-form>
            </form>
        </div>
        <div class="row mb-2">
            <div class="col-md-1">
                <a href="{{ route('admin.purchase-orders.index') }}" class="col-12 col-md-2"><button class="btn btn-secondary">Cancel</button></a>
            </div>
            <div class="col-md-11 text-end">
                <form action="{{ route('admin.purchase-orders.destroy', [$purchaseOrder->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the purchase order?')">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
