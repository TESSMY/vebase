@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Edit Quotation Request</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                  <li class="breadcrumb-item"><a href="{{ route('admin.quotation-requests.edit', [$quotationRequest->getRouteKey()]) }}">Quotation Request</a></li>
                  <li class="breadcrumb-item active">Edit Quotation Request</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <form action="{{ route('admin.quotation-requests.update', [$quotationRequest->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <quotation-request-form :taxRate="7" :quotation-request="{{ $quotationRequest->load('quotationRequestItems.product', 'quotationRequestItems.productVariant', 'supplier') }}"></quotation-request-form>
            </form>
        </div>
        <div class="row">
            <div class="col-md-1">
                <a href="{{ route('admin.quotation-requests.index') }}" class="col-12 col-md-2"><button class="btn btn-danger m-2">Cancel</button></a>
            </div>
            <div class="col-md-11 text-end">
                @if ($quotationRequest->status == App\Models\QuotationRequest::STATUS_DRAFT || $quotationRequest->status == App\Models\QuotationRequest::STATUS_PENDING)
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-success my-2 ms-0" data-bs-toggle="modal" data-bs-target="#generatePoModal">
                        Generate P.O
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="generatePoModal" tabindex="-1" aria-labelledby="generatePoModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="generatePoModalLabel">Confirmation to Generate Purchase Order?</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row p-2">
                                        <div class="col-md-4 md:text-right">
                                            <label class="py-2">Quotation Request ID: </label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" value="{{ $quotationRequest->id }}" class="form-control" disabled/>
                                        </div>
                                        <div class="col-md-4 md:text-right">
                                            <label class="py-2">Date: </label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" value="{{ now()->format('d M Y') }}" class="form-control" disabled/>
                                        </div>
                                    </div>
                                    <div class="row p-2">
                                        <div class="col-md-4 md:text-right">
                                            <label class="py-2">Product Details: </label>
                                        </div>
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th scope="col">Product</th>
                                                <th scope="col">SKU</th>
                                                <th scope="col">Quantity</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($quotationRequest->quotationRequestItems as $item)
                                                    <tr>
                                                        <th scope="row">{{ $item->name }}</th>
                                                        <td>{{ $item->sku }}</td>
                                                        <td>{{ $item->quantity }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a class="ms-1" href="{{ route('admin.quotation-requests.generatePo', [$quotationRequest->getRouteKey()]) }}"><button type="button" class="btn btn-success my-2 ms-0">Generate P.O.</button></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.quotation-requests.destroy', [$quotationRequest->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the quotation request?')">Delete</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
