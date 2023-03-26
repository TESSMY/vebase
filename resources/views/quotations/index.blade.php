@extends('layouts/layout')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">
                        {{ __('Quotations') }}
                    </span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Quotations</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row mb-3">
                <a href="{{ route('admin.quotations.create') }}" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-primary rounded"><i class="uil-plus-circle"></i> Create New Quotation </a>
            </div>
            <form action="{{ route('admin.quotations.index') }}" method="GET" id="form">
                @csrf
                <div class="row mb-3">
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">Display:</span>
                        <select class="form-select mx-2" name="limit">
                            <option value="10" {{ $limit == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $limit == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $limit == '50' ? 'selected' : '' }}>50</option>
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
                        <tr class="fw-bold">
                            <td>Quotation No.</td>
                            <td>Name </td>
                            <td>Delivery Date</td>
                            <td>Client</td>
                            <td>Total</td>
                            <td>Order Status</td>
                            <td>Created On</td>
                            <td>Action</td>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($quotations as $quotation)
                        <tr>
                            <td>Q #{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $quotation->name }}</td>
                            <td>{{ $quotation->delivery_date }}</td>
                            <td>{{ $quotation->client->name }}</td>
                            <td>$ {{ $quotation->grand_total }}</td>
                            <td>
                                @if($quotation->status == \App\Models\Quotation::STATUS_DRAFT)
                                    Draft
                                @elseif($quotation->status == \App\Models\Quotation::STATUS_PENDING)
                                    Pending
                                @elseif($quotation->status == \App\Models\Quotation::STATUS_APPROVED)
                                    Approved
                                @elseif($quotation->status == \App\Models\Quotation::STATUS_SENT)
                                    Sent
                                @elseif($quotation->status == \App\Models\Quotation::STATUS_ORDER_CONFIRMED)
                                    Order Confirmed
                                @elseif($quotation->status == \App\Models\Quotation::STATUS_EXPIRED)
                                    Expired
                                @endif
                            </td>
                            <td>{{ $quotation->created_at }}</td>
                            <td>
                                @can('view-quotation')
                                    <a href="{{ route('admin.quotations.show', [$quotation->getRouteKey()]) }}"><i class="uil-eye mx-1"></i></a>
                                @endcan
                                @can('edit-quotation')
                                    <a href="{{ route('admin.quotations.edit', [$quotation->getRouteKey()]) }}"><i class="uil-edit"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="text-center">There are no quotations found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2">
            {{ $quotations->links() }}
        </div>
    </div>
@endsection

