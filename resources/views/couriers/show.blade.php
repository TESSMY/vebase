@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">View Couriers</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route('admin.couriers.index') }}">Courier List</a></li>
                    <li class="breadcrumb-item active">View Courier</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>

        {{--Courier Information--}}
        <div class="bg-white card shadow py-3 px-4">
            <div>
                <div class="header-card shadow my-3" style="background-color: #727CF5;">
                    <div class="card-body row">
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-4 px-2">
                                    <div class="col-md-4 px-2">
                                        @if (!empty($courier->image))
                                            <img src="{{ $courier->image }}" alt="Avatar" class="rounded-circle">
                                        @else
                                            <img src="/images/avatar.jpg" alt=""  class="rounded-circle" height="50" width="50">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row" style="color: #F9F6EE;">{{ $courier->name }}</div>
                                    <div class="row" style="color: #F9F6EE;">{{ $courier->code }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card-group row">
                                <div class="card col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">{{ __('PENDING') }}</h6>
                                        <h5 class="card-text text-muted">{{ $courier->total_shipment_pending ?? 0 }}</h5>
                                    </div>
                                </div>
                                <div class="card col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">{{ __('ON GOING') }}</h6>
                                        <h5 class="card-text text-muted">{{ $courier->total_shipment_ongoing ?? 0 }}</h5>
                                    </div>
                                </div>
                                <div class="card col-sm-3 me-1">
                                    <div class="card-body">
                                        <h6 class="card-subtitle text-muted fs-6">{{ __('COMPLETED') }}</h6>
                                        <h5 class="card-text text-muted">{{ $courier->total_shipment_completed ?? 0 }}</h5>
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
                                <div class="col-md-9">
                                    <h5 class="card-title align-content-center">COURIER INFORMATION</h5>
                                </div>
                                <div class="col-md-3 align-middle text-end">
                                    <a href="{{ route('admin.couriers.edit', $courier) }}">
                                        <span class="btn btn-sm-light uil-edit-alt px-1"> Edit </span>
                                    </a>
                                </div>
                            </div>
                            <p class="card-text text-muted">Courier Company Name : {{ $courier->name }}</p>
                            <p class="card-text text-muted">Courier Code : {{ $courier->code }}</p>
                            <p class="card-text text-muted">Courier Phone : {{ $courier->phone }}</p>
                            <p class="card-text text-muted">Contact Name : {{ $courier->contact_name }}</p>
                            <p class="card-text text-muted">Contact Number : {{ $courier->contact_number }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card" style="width: auto;">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="card-title align-middle py-lg-2">SHIPMENT ORDERS</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="fs-5 fw-normal">
                                            <tr>
                                                <th class="px-4 text-muted" style="width:10%">Shipment ID</th>
                                                <th class="px-4 text-muted" style="width:20%">Shipment Value</th>
                                                <th class="px-4 text-muted" style="width:30%">Shipment Date</th>
                                                <th class="px-4 text-muted" style="width:30%">ETA Date</th>
                                                <th class="px-4 text-muted" style="width:10%">Order Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse ($courier->shipments as $shipment)
                                                <tr>
                                                    <td class="ps-4">{{ $shipment->id }}</td>
                                                    <td>{{ $shipment->value }}</td>
                                                    <td>{{ $shipment->shipment_date }}</td>
                                                    <td>{{ $shipment->eta_date }}</td>
                                                    <td>{{ $shipment->status }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="100%" class="text-center">There are No Shipment Orders found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="float-end">
                                        <a href="{{ route('admin.shipments.index') }}" class="card-link">View All</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @can('delete-courier')
                    <hr>
                    <form action="{{ route('admin.couriers.destroy', [$courier->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
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

