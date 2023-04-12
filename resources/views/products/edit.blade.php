@extends('layouts/layout')

@section('content')
    <div class="container-fluid" id="app">
        {{-- header --}}
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">
                        {{ __('Edit Product') }}
                    </span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Product List</a></li>
                    <li class="breadcrumb-item active">Edit Product</li>
                </ol>
            </nav>
        </div>

        {{-- form --}}
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <product-form :edit_product="{{ $product ?? 'undefined' }}" :variants="{{ $product->productVariants }}"></product-form>
            <div class="mt-3">
                <button class="btn btn-primary me-2" type="submit">{{ __('Update') }}</button>
                <button class="btn btn-secondary">{{ __('Close') }}</button>
            </div>
        </form>
        <hr>
        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" enctype="multipart/form-data">
            @method('DELETE')
            @csrf
            <div class="text-end">
                <button class="btn btn-danger px-2" type="submit">
                    Delete
                </button>
            </div>
        </form>
    </div>
@endsection

