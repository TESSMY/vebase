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
        <div class="bg-white card shadow">
           <div class="card-body">
                <form action="{{ route($routePrefix . '.' . $routeName . '.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <span class="h5">Information</span>
                    </div>
                    <div class="border mb-2"></div>

                    @includeFirst($routePrefix . '.' . $routeName . '.form', 'vebase::form')
                    <div class="d-flex">
                        <button type="submit" class="btn btn-success me-2">Create</button>
                        <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="btn btn-dark">Back</a>
                    </div>
                </form>
           </div>
        </div>
    </div>
@endsection
