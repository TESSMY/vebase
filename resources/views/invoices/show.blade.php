@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">View {{ $modelName }}</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                  <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.' . $routeName . '.index') }}">{{ $modelName }}</a></li>
                  <li class="breadcrumb-item active">View {{ $modelName }}</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row border-bottom mb-2">
                <span class="h5">Information</span>
            </div>
            <iframe src="{{ $$routeModel->url }}" class="w-100 vh-100">Your browser isn't compatible</iframe>
            <div class="row col-12">
                <a href="{{ route($routePrefix . '.' . $routeName . '.edit', $$routeModel->getRouteKey()) }}" class="col-12 col-md-1 btn btn-success m-2">Edit</a>
                <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
                @if (Auth::user()->hasPermissionTo('delete-invoice'))
                    <form action="{{ route($routePrefix . '.' . $routeName . '.destroy', $$routeModel->getRouteKey()) }}" class="col-12 col-md-1 m-2 px-0" method="POST">
                        @method('DELETE')
                        @csrf
                        <button class="col-12 btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete? You cannot revert this.')">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
