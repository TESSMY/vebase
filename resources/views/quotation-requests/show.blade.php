@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Generate Quotation Request</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                  <li class="breadcrumb-item"><a href="{{ route('admin.purchase-orders.index') }}">Quotation Request List</a></li>
                  <li class="breadcrumb-item active">Generate Quotation Request</li>
                </ol>
            </nav>
        </div>
        <div class="row">
            <div class="col-12 col-md-1">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quotation-request-modal">Send R.F.Q</button>
                <div class="modal fade" id="quotation-request-modal" role="dialog">
                    <div class="modal-dialog">
                        <form action="{{ route('admin.quotation-requests.send', [$quotationRequest->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" style="float: left;">Send Quotation Request {{ $quotationRequest->id }}
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row p-2">
                                        <div class="col-md-4 md:text-right">
                                            <label class="py-2">Customer Name: </label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" value="{{ $quotationRequest->supplier->name }}" readonly disabled />
                                        </div>
                                    </div>
                                    <div class="row p-2">
                                        <div class="col-md-4 md:text-right">
                                            <label class="py-2">To Email: </label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="email" name="to_email" class="form-control" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">
                                        Close
                                    </button>
                                    <button type="submit" class="btn btn-primary ml-auto">
                                        Submit Quotation Request
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-1">
                <a href="{{ route('admin.quotation-requests.edit', [$quotationRequest->getRouteKey()]) }}"><button type="button" class="btn btn-primary">Edit</button></a>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <iframe src="{{ $quotationRequest->file_url }}" frameborder="0" class="w-100 mx-auto" height="800px"></iframe>
        </div>
    </div>
@endsection
