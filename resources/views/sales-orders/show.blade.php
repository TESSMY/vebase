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
            @else
                <form action="{{ route('admin.sales-orders.generateOrder', [$salesOrder->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
            @endif
                @csrf
                <sales-order-show-form :tax_rate="7" :sales-order="{{ $salesOrder->load('salesOrderItems.product', 'salesOrderItems.productVariant', 'client') }}"></sales-order-show-form>
            </form>
        </div>
    </div>
@endsection
