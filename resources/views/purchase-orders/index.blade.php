@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <span class="page-title h4">Purchase Order</span>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row mb-3">
                <a href="{{ route('admin.purchase-orders.create') }}" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-primary rounded"><i class="uil-plus-circle"></i> Create New Purchase Order </a>
            </div>
            <form action="{{ route('admin.purchase-orders.index') }}" method="GET" id="form">
                @csrf
                <div class="row mb-3">
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">Display:</span>
                        <select class="form-select mx-1" name="limit">
                            <option selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="my-auto">Items</span>
                    </div>
                    <div class="col-md-6"></div>
                    <div class="col-12 col-md-2 p-0">
                        <div class="px-2">
                            <label class="form-label my-auto me-md-2">Search: </label>
                            <input class="form-control" type="search" placeholder="Search" name="search" value="{{ request()->input('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <span class="my-auto">Status:</span>
                        <select class="form-select mx-1" name="limit">
                            <option selected>Choose...</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="overflow-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <td>Purchase Order ID</td>
                            <td>No. of Product</td>
                            <td>Supplier</td>
                            <td>Total</td>
                            <td>Order Status</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseOrders as $purchaseOrder)
                            <tr>
                                <td>{{ $purchaseOrder->id }}</td>
                                <td>{{ $purchaseOrder->item_count }}</td>
                                <td>{{ !empty($purchaseOrder->supplier) ? $purchaseOrder->supplier->name : '' }}</td>
                                <td>{{ $purchaseOrder->grand_total }}({{ $purchaseOrder->currency }})</td>
                                <td>{{ $purchaseOrder->status }}</td>
                                <td>
                                    <a href="{{ route('admin.purchase-orders.show', [$purchaseOrder->getRouteKey()]) }}"><button type="button" class="btn btn-primary">View</button></a>
                                    <a href="{{ route('admin.purchase-orders.edit', [$purchaseOrder->getRouteKey()]) }}"><button type="button" class="btn btn-primary">Edit</button></a>
                                    <a href="{{ route('admin.purchase-orders.destroy', [$purchaseOrder->getRouteKey()]) }}"><button type="button" class="btn btn-danger">Delete</button></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center">There are no purchase orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2">
            {{ $purchaseOrders->links() }}
        </div>
    </div>
@endsection