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
        @if (View::exists($routePrefix . '.' . $routeName . '.show-body'))
            @include($routePrefix . '.' . $routeName . '.show-body')
        @else
            <div class="bg-white card shadow">
                <div class="card-body">
                    <div class="row">
                        <span class="h5">Information</span>
                    </div>
                    <div class="border mb-2"></div>
                    <div class="row mb-2">
                        @if (!empty($model->showFields))
                            @foreach ($model->showFields as $showField)
                                <span class="col-4 mb-2">{{ $showField['displayName'] }}: </span><span class="col-8 mb-2">{{ $$routeModel[$showField['columnName']] }}</span>
                            @endforeach
                        @endif
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>
                            @can('update', $$routeModel)
                                <a href="{{ route($routePrefix . '.' . $routeName . '.edit', $$routeModel->getRouteKey()) }}" class="btn btn-success me-3">Edit</a>
                            @endcan
                            <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="btn btn-dark me-3">Back</a>
                        </div>
                        @can('delete', $$routeModel)
                            <form action="{{ route($routePrefix . '.' . $routeName . '.destroy', $$routeModel->getRouteKey()) }}" method="POST" enctype="multipart/form-data">
                                @method('DELETE')
                                @csrf
                                <button class="btn btn-danger px-2" type="submit" onclick="return confirm('Are you sure you want to delete? You cannot revert this.')">
                                    Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
