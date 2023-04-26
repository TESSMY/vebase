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

        {{--Client Information--}}
        <div class="bg-white card shadow py-3 px-4">
            <div>
                <div class="header-card shadow my-3" style= "background-color: #727CF5;">
                    <div class="card-body row">
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-4 px-2">
                                    @if (!empty($client->image))
                                        <img src="{{ $client->image }}" alt="Avatar" class="rounded-circle">
                                    @else
                                        <img src="/images/avatar.jpg" alt="" class="rounded-circle" height="50" width="50">
                                    @endif
                                </div>
                                <div class="col-md-8">
                                    <div class="row" style="color: #F9F6EE;">{{ $client->name }}</div>
                                    <div class="row" style="color: #F9F6EE;">{{ $client->email }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card-group row">
                                <div class="card col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">TOTAL ORDERS</h6>
                                        <h5>{{ $client->salesReports()->where('year', date('Y'))->where('month', date('n'))->whereNull('day')->sum('total_sales_order') }}</h5>
                                        <div class="d-flex justify-content-start">
                                            <h8 class="text-muted"><span class="badge rounded-pill bg-warning">-20%</span> This month </h8>
                                        </div>
                                    </div>
                                </div>
                                <div class="card col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">REVENUE (THIS MONTH)</h6>
                                        <h5>{{ $client->salesReports()->whereNull('day')->where('month', date('n'))->where('year', date('Y'))->sum('total_revenue') }}</h5>
                                        <div class="d-flex justify-content-start">
                                            <h8 class="text-muted"><span class="badge rounded-pill bg-danger">-50%</span> This month </h8>
                                        </div>
                                    </div>
                                </div>
                                <div class="card col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">REVENUE (PAST 6 MONTHS)</h6>
                                        @php
                                            $revenue = 0;
                                            if (date('Y', strtotime('-6 months')) != date('Y')) {
                                               $prevYear = $client->salesReports()->where('year', date('Y', strtotime('-6 months')))
                                               ->where('month', '>=', date('n', strtotime('-6 months')))
                                               ->sum('total_revenue');

                                               $currentYear = $client->salesReports()->where('year', date('Y'))
                                               ->where('month', '>=', 1)
                                               ->where('month', '<=', date('n'))
                                               ->sum('total_revenue');

                                               $revenue = $prevYear + $currentYear;
                                            } else {
                                               $revenue = $client->salesReports()->where('year', date('Y'))
                                               ->where('month', '>=', date('n', strtotime('-6 months')))
                                               ->where('month', '<=', date('n'))
                                               ->sum('total_revenue');
                                            }
                                        @endphp
                                        <h5>{{ number_format($revenue, 2) }}</h5>
                                        <div class="d-flex justify-content-start">
                                            <h8 class="text-muted"><span class="badge rounded-pill bg-info">20%</span> Past 6 Months </h8>
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
                                    <h5 class="card-title align-middle py-lg-2">CLIENT'S INFORMATION</h5>
                                </div>
                                <div class="col-md-3 align-middle text-end">
                                    <a href="{{ route('admin.clients.edit', $client) }}">
                                        <span class="btn btn-light uil-edit-alt px-2"> Edit </span>
                                    </a>
                                </div>
                            </div>
                            <p class="card-text text-muted">Full Name : {{ $client->name }}</p>
                            <p class="card-text text-muted">Company Name : {{ $client->company_name }}</p>
                            <p class="card-text text-muted">Email : {{ $client->email }}</p>
                            <p class="card-text text-muted">Mobile : {{ $client->phone }}</p>
                            <p class="card-text text-muted">Address : {{ $client->full_address }}</p>
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
                                    <div class="table-responsive-sm">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th class="px-4 text-muted" style="width: auto;">Sales Order ID</th>
                                                <th class="px-4 text-muted" style="width: auto;">Date</th>
                                                <th class="px-4 text-muted" style="width: auto;">Status</th>
                                                <th class="px-4 text-muted" style="width: auto;">Amount(SGD)</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse ($client->salesOrders as $salesOrder)
                                                <tr>
                                                    <td class="ps-4">{{ $salesOrder->id }}</td>
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
                                    </div>
                                    <div class="float-end">
                                        <a href="{{ route('admin.sales-orders.index') }}" class="card-link">View All</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @can('delete-client')
                    <hr>
                    <form action="{{ route('admin.clients.destroy', [$client->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                        @method('DELETE')
                        @csrf
                        <div class="text-end">
                            <button class="btn btn-danger px-2" type="submit">
                                Delete
                            </button>
                        </div>
                    </form>
                @endcan
            </div>
        </div>
    </div>
@endsection
