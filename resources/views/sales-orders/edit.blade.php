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
                  <li class="breadcrumb-item"><a href="{{ route('admin.sales-orders.edit', [$salesOrder->getRouteKey()]) }}">Sales Order</a></li>
                  <li class="breadcrumb-item active">Edit Sales Order</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <form action="{{ route('admin.sales-orders.update', [$salesOrder->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <sales-order-form :tax-rate="{{ $taxRate }}" :sales-order="{{ $salesOrder }}"></sales-order-form>
            </form>
        </div>
    </div>
@endsection