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
            <div class="col-md-12 text-end">
                <a class="ms-1" href="{{ route('admin.quotation-requests.generatePo', [$quotationRequest->getRouteKey()]) }}"><button type="button" class="btn btn-success my-2 ms-0">Generate P.O.</button></a>
                <form action="{{ route('admin.quotation-requests.destroy', [$quotationRequest->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-danger ms-1">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection
