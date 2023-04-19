@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Add New Quotation</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route('admin.quotations.index') }}">Quotations</a></li>
                    <li class="breadcrumb-item active">Create New Quotation</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            @can('create', \App\Models\Quotation::class)
                <form action="{{ route('admin.quotations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <quotation-form></quotation-form>
                    <div class="mt-3">
                        <button class="btn btn-primary me-2" type="submit">{{  __('Create') }}</button>
                        <button class="btn btn-secondary">{{ __('Close') }}</button>
                    </div>
                </form>
            @endcan
        </div>
    </div>
@endsection
