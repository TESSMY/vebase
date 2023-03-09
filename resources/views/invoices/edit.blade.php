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
        <form action="{{ route($routePrefix . '.' . $routeName . '.update', $$routeModel->getRouteKey()) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <invoice-form :product-variants="{{ json_encode($productVariants) }}" :sales-orders="{{ json_encode($salesOrders) }}" :tax-rate="{{ $taxRate }}"></invoice-form>
        </form>
    </div>
@endsection

