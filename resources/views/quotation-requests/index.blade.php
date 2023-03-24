@extends('layouts/layout')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <span class="page-title h4">Request for Quotations</span>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row mb-3">
                <a href="{{ route('admin.quotation-requests.create') }}" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-primary rounded"><i class="uil-plus-circle"></i> Create New Quotation Request </a>
            </div>
            <form action="{{ route('admin.quotation-requests.index') }}" method="GET" id="form">
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
                            <td>RFQ Number</td>
                            <td>Delivery Date</td>
                            <td>Supplier</td>
                            <td>Total</td>
                            <td>Order Status</td>
                            <td>Created By</td>
                            <td>Created On</td>
                            <td>Action</td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quotationRequests as $quotationRequest)
                            <tr>
                                <td>RFQ #{{ $quotationRequest->id }}</td>
                                <td>{{ $quotationRequest->delivery_date }}</td>
                                <td>{{ $quotationRequest->supplier?->name }}</td>
                                <td>${{ $quotationRequest->grand_total }}</td>
                                <td>{{ $quotationRequest->status }}</td>
                                <td>{{ $quotationRequest->createdBy?->name }}</td>
                                <td>{{ $quotationRequest->created_at }}</td>
                                <td>
                                    <a href="{{ route('admin.quotation-requests.show', [$quotationRequest->getRouteKey()]) }}"><button type="button" class="btn btn-primary">View</button></a>
                                    <a href="{{ route('admin.quotation-requests.edit', [$quotationRequest->getRouteKey()]) }}"><button type="button" class="btn btn-primary">Edit</button></a>
                                    <form action="{{ route('admin.quotation-requests.destroy', [$quotationRequest->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                                        @method('DELETE')
                                        @csrf
                                        <button class="btn btn-danger px-4" type="submit" onclick="return confirm('Are you sure you want to delete? You cannot revert this.')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center">There are no quotation requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2">
            {{ $quotationRequests->links() }}
        </div>
    </div>
@endsection
