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
        <div class="bg-white card shadow">
            <div class="card-body">
                <form action="{{ route($routePrefix . '.' . $routeName . '.update', $$routeModel->getRouteKey()) }}" method="POST" enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <div class="row">
                        <span class="h5">Information</span>
                    </div>
                    <div class="border mb-2"></div>
                    @includeFirst([$routePrefix . '.' . $routeName . '.form', 'vebase::form'])
                    <div class="d-flex">
                        <button type="submit" class="btn btn-success me-2">Update</button>
                        <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="btn btn-dark">Back</a>
                    </div>
                </form>
                <form action="{{ route($routePrefix . '.' . $routeName . '.destroy', $$routeModel) }}" style="margin-top: -65px" method="POST" enctype="multipart/form-data">
                    @method('DELETE')
                    @csrf
                    <div class="mt-3 text-end">
                        <button class="btn btn-danger px-2" type="submit" onclick="return confirm('Are you sure you want to delete? You cannot revert this.')">
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
