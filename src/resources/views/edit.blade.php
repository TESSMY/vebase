@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Edit {{ $modelName }}</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                  <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.' . $routeName . '.index') }}">{{ $modelName }}</a></li>
                  <li class="breadcrumb-item active">Edit {{ $modelName }}</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <form action="{{ route($routePrefix . '.' . $routeName . '.update', $$routeModel->getRouteKey()) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <div class="row border-bottom mb-2">
                    <span class="h5">Information</span>
                </div>
                @include('vebase::form')
                <div class="row col-12">
                    <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Update</button>
                    <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
                </div>
            </form>
        </div>
    </div>
@endsection
