@extends('layouts/layout')
@section('content')
    <div class="container-fluid" id="app">
        {{-- header --}}
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">
                         @if(isset($product))
                            {{ __('Edit Product') }}
                        @else
                            {{ __('Create Product') }}
                        @endif
                    </span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    @if(isset($product))
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Product List</a></li>
                        <li class="breadcrumb-item active">Edit Product</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Product List</a></li>
                        <li class="breadcrumb-item active">Create New Product</li>
                    @endif
                </ol>
            </nav>
        </div>

        {{-- form --}}
        @if(isset($product) && !empty($product))
            <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @else
                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @endif
                        @csrf
                        <product-form :suppliers="{{ $suppliers }}" :products="{{ $products ?? 'undefined' }}" :edit_product="{{ $product ?? 'undefined' }}" :brands="{{ $brands }}" :variants="{{ $variants }}" :product_bundles="{{ $bundles ?? 'undefined' }}"></product-form>
                        <div class="mt-3">
                            <button class="btn btn-primary me-2" type="submit">{{ isset($product) ? __('Update') : __('Create') }}</button>
                            <button class="btn btn-secondary">{{ __('Close') }}</button>
                        </div>
                    </form>
            </form>
            <hr>
            @if(isset($product) && !empty($product))
                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" enctype="multipart/form-data">
                    @method('DELETE')
                    @csrf
                    <div class="text-end">
                        <button class="btn btn-danger px-2" type="submit">
                            Delete
                        </button>
                    </div>
                </form>
            @endif
    </div>
@endsection

