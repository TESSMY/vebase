@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Add New Quotation Request</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                  <li class="breadcrumb-item"><a href="{{ route('admin.quotation-requests.index') }}">Quotation Request</a></li>
                  <li class="breadcrumb-item active">Create New Quotation Request</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <form action="{{ route('admin.quotation-requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <quotation-request-form :taxRate="{{ $taxRate }}"></quotation-request-form>
            </form>
        </div>
    </div>
@endsection
