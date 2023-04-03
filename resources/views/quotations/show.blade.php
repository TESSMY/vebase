@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Generate Quotation</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route('admin.quotations.index') }}">Quotation List</a></li>
                    <li class="breadcrumb-item active">Generate Quotation</li>
                </ol>
            </nav>
        </div>
        <div class="row">
            <div class="col-12 col-md-1">
                <span type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quotationModal">
                    Send Quotation Email
                </span>

                <!-- Modal -->
                <div class="modal fade" id="quotationModal" tabindex="-1" aria-labelledby="quotationModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('admin.quotations.send', [$quotation->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" style="float: left;">Send Quotation {{ $quotation->id }}
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row p-2">
                                        <div class="col-md-4 md:text-right">
                                            <label class="py-2">Customer Name: </label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" value="{{ $quotation->client->name }}" readonly disabled />
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
                                    <button type="button" class="btn btn-default" data-dismiss="modal">
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
                <a href="{{ route('admin.quotations.edit', [$quotation->getRouteKey()]) }}"><button type="button" class="btn btn-primary">Edit</button></a>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <iframe src="{{ $quotation->file_url }}" frameborder="0" class="w-100 mx-auto" height="800px"></iframe>
        </div>
    </div>
@endsection
