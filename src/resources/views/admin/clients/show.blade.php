@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">View Client</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route('admin.clients.index') }}">Client List</a></li>
                    <li class="breadcrumb-item active">View Client</li>
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
                                    <div class="avatar-wrapper">
                                        @if (!empty($client->image))
                                            <img src="{{ $client->image }}" alt="Avatar" class="avatar">
                                        @else
                                            <img src=" " class="rounded object-fit-center" height="50" width="50" />
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row" style="color: #F9F6EE;">{{ $client->name }}</div>
                                    <div class="row" style="color: #F9F6EE;">{{ $client->email }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card-group row">
                                <div class="card text-center col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">NUMBER OF ORDERS</h6>
                                        <h5>{{ $client->salesOrders->count() }}</h5>
                                    </div>
                                </div>
                                <div class="card text-center col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">AVERAGE REVENUE</h6>
                                        @if (($client->salesOrders->count()) != 0)
                                            <h5 class="card-text text-muted">{{($client->saleOrders->sum('grand_total')) / ($client->salesOrders->count())}} </h5>
                                        @else
                                            <h5> 0 </h5>
                                        @endif
                                    </div>
                                </div>
                                <div class="card text-center col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">AVERAGE ANNUAL REVENUE</h6>
                                        @if (($client->salesOrders->count()) != 0)
                                            <h5 class="card-text text-muted">{{($client->saleOrders->sum('grand_total')) }} </h5> //??
                                        @else
                                            <h5> 0 </h5>
                                        @endif
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
                                    <h5 class="card-title align-middle py-lg-2">CLIENT'S INFORMATION</h5>
                                </div>
                                <div class="col-md-3 align-middle text-end py-lg-2">
                                    <a href="{{ route('admin.clients.edit', $client) }}">
                                        <i class="uil-edit-alt"> Edit </i>
                                    </a>
                                </div>
                            </div>
                            <p class="card-text text-muted">Full Name : {{ $client->name }}</p>
                            <p class="card-text text-muted">Company Name : {{ $client->company_name }}</p>
                            <p class="card-text text-muted">Email : {{ $client->email }}</p>
                            <p class="card-text text-muted">Mobile : {{ $client->phone }}</p>
                            <p class="card-text text-muted">Address : {{ $client->address_1 }}, {{ $client->address_2 }}, {{ $client->city }} {{ $client->postcode }} {{ $client->state }} {{ $client->country }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card" style="width: auto;">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title align-middle py-lg-2">NUMBER OF ORDERS AND REVENUE</h5>
                                    //
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="width: auto;">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="card-title align-middle py-lg-2">SALES ORDERS</h5>
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th class="px-4 text-muted" style="width:20%">Sales Order ID</th>
                                            <th class="px-4 text-muted" style="width:30%">Customer Name</th>
                                            <th class="px-4 text-muted" style="width:20%">Date</th>
                                            <th class="px-4 text-muted" style="width:15%">Status</th>
                                            <th class="px-4 text-muted" style="width:15%">Amount(SGD)</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($client->salesOrders as $salesOrder)
                                            <tr>
                                                <td class="ps-4">{{ $salesOrder->id }}</td>
                                                <td>{{ $salesOrder->customer_po }}</td>
                                                <td>{{ $salesOrder->date }}</td>
                                                <td>{{ $salesOrder->status }}</td>
                                                <td>{{ $salesOrder->grand_total }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="100%" class="text-center">There are No Sales Orders found.</td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                    <div class="float-end">
                                        <a href="{{ route('admin.sales-orders.index') }}" class="card-link">View All</a>
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

        .avatar {
            vertical-align: middle;
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }
    </style>
@endpush
