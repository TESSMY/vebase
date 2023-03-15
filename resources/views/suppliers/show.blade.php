@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">View Supplier</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">Supplier List</a></li>
                    <li class="breadcrumb-item active">View Supplier</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>

        {{--Supplier Information--}}
        <div class="bg-white card shadow py-3 px-4">
            <div>
                <div class="header-card shadow my-3" style= "background-color: #727CF5;">
                    <div class="card-body row">
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-4 px-2">
                                    <div class="col-md-4 px-2">
                                        @if (!empty($supplier->image))
                                            <img src="{{ $supplier->image }}" alt="Avatar" class="rounded-circle">
                                        @else
                                            <img src="/images/avatar.jpg" alt=""  class="rounded-circle" height="50" width="50">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row" style="color: #F9F6EE;">{{ $supplier->name }}</div>
                                    <div class="row" style="color: #F9F6EE;">{{ $supplier->code }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card-group row">
                                <div class="card col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">NUMBER OF PURCHASED ORDERS</h6>
                                        <h5 class="card-text">{{ $supplier->purchaseOrders->count() }}</h5>
                                        <div class="d-flex justify-content-start">
                                            <h8 class="text-muted"><span class="badge rounded-pill bg-warning">-20%</span> This month </h8>
                                        </div>
                                    </div>
                                </div>
                                <div class="card col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">AVERAGE PRODUCTS ORDERED</h6>
                                        @if (($supplier->purchaseOrders->count()) != 0)
                                            <h5 class="card-text text-muted">{{ ($supplier->purchaseOrders->sum('item_count')) / ($supplier->purchaseOrders->count()) }}</h5>
                                        @else
                                            <h5> 0 </h5>
                                        @endif
                                        <div class="d-flex justify-content-start">
                                            <h8 class="text-muted"><span class="badge rounded-pill bg-danger">-50%</span> This month </h8>
                                        </div>
                                    </div>
                                </div>
                                <div class="card col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">AVERAGE ANNUAL COST</h6>
                                        @if (($supplier->purchaseOrders->count()) != 0)
                                            <h5 class="card-text text-muted">{{ ($supplier->purchaseOrders->sum('grand_total')) }}</h5> //only cost for one month
                                        @else
                                            <h5> 0 </h5>
                                        @endif
                                        <div class="d-flex justify-content-start">
                                            <h8 class="text-muted"><span class="badge rounded-pill bg-info">20%</span> This year </h8>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title align-middle py-lg-2">SUPPLIER INFORMATION</h5>
                                </div>
                                <div class="col-md-3 align-middle text-end">
                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}">
                                        <span class="btn btn-light uil-edit-alt px-2"> Edit </span>
                                    </a>
                                </div>
                            </div>
                            <p class="card-text text-muted">Supplier Company Name : {{ $supplier->name }}</p>
                            <p class="card-text text-muted">Supplier Code : {{ $supplier->code }}</p>
                            <p class="card-text text-muted">Email : {{ $supplier->email }}</p>
                            <p class="card-text text-muted">Contact Name : {{ $supplier->contact_name }}</p>
                            <p class="card-text text-muted">Contact Number : {{ $supplier->contact_number }}</p>
                            <p class="card-text text-muted">Address : {{ $supplier->address_1 }}, {{ $supplier->address_2 }}, {{ $supplier->city }} {{ $supplier->postcode }} {{ $supplier->state }} {{ $supplier->country }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card" style="width: auto;">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title align-middle py-lg-2">COST AND PURCHASE ORDERS</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="width: auto;">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="card-title align-middle py-lg-2">PURCHASE ORDERS</h5>
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th class="px-4 text-muted" style="width:30%">Purchase Order ID</th>
                                            <th class="px-4 text-muted" style="width:30%">No of Product</th>
                                            <th class="px-4 text-muted" style="width:20%">Total</th>
                                            <th class="px-4 text-muted" style="width:20%">Order Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($supplier->purchaseOrders as $purchaseOrder)
                                            <tr>
                                                <td class="ps-4">{{ $purchaseOrder->id }}</td>
                                                <td>{{ $purchaseOrder->item_count }}</td>
                                                <td>{{ $purchaseOrder->grand_total }}</td>
                                                <td>{{ $purchaseOrder->status }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="100%" class="text-center">There are No Purchase Orders found.</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                    <div class="float-end">
                                        <a href="{{ route('admin.purchase-orders.index') }}" class="card-link">View All</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .header-card {
            background-color: rgb(114,124,245);
        }

    </style>
@endpush
