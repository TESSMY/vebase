@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <span class="page-title h4">Sales Order</span>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row mb-3">
                <a href="{{ route('admin.sales-orders.create') }}" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-primary rounded"><i class="uil-plus-circle"></i> Create New Sales Order </a>
            </div>
            <form action="#" method="GET" id="form">
                @csrf
                <div class="row mb-3">
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">Display:</span>
                        <select class="form-select mx-1" name="limit">
                            <option selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="my-auto">Rows</span>
                    </div>
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">Filter:</span>
                        <select class="form-select mx-1" name="filter">
                            <option selected>Choose...</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div class="col-md-4"></div>
                    <div class="col-12 col-md-4 p-0">
                        <div class="px-2">
                            <label class="form-label my-auto me-md-2">Search: </label>
                            <input class="form-control" type="search" placeholder="Search" name="search" value="{{ request()->input('search') }}">
                        </div>
                    </div>
                </div>
            </form>
            <div class="overflow-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <td>Sales Order ID</td>
                            <td>Customer Name</td>
                            <td>Date</td>
                            <td>Status</td>
                            <td>Amount</td>
                            <td>Delivery Order</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesOrders as $salesOrder)
                            <tr>
                                <td>{{ $salesOrder->id }}</td>
                                <td>{{ $salesOrder->user?->name }}</td>
                                <td>{{ $salesOrder->date }}</td>
                                <td>{{ $salesOrder->status }}</td>
                                <td>{{ $salesOrder->amount }}({{ $salesOrder->currency }})</td>
                                <td>-</td>
                                <td>
                                    <a href="{{ route('admin.sales-orders.edit', [$salesOrder->getRouteKey()]) }}"><button type="button" class="btn btn-primary">View</button></a>
                                    <a href="{{ route('admin.sales-orders.destroy', [$salesOrder->getRouteKey()]) }}"><button type="button" class="btn btn-danger">Delete</button></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center">There are no sales orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2">
            {{ $salesOrders->links() }}
        </div>
    </div>
@endsection
