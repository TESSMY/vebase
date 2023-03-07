@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Create New {{ $modelName }}</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                  <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.' . $routeName . '.index') }}">{{ $modelName }}</a></li>
                  <li class="breadcrumb-item active">Create New {{ $modelName }}</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <form action="{{ route($routePrefix . '.' . $routeName . '.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-white card shadow py-3 px-4">
                <div class="row border-bottom mb-2">
                    <span class="h5">{{ $modelName }} Information</span>
                </div>
                @include('admin.invoices.form')
            </div>
            <div class="border my-2 mb-3"></div>
            <div class="bg-white card shadow py-3 px-4">
                <invoice-form></invoice-form>
                <div class="row col-12">
                    <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Create</button>
                    <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
                </div>
            </div>
        </form>
    </div>
@endsection
