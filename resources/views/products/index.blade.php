@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <span class="page-title h4">Products</span>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row mb-3">
                <a href="{{ route('admin.products.create') }}" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-primary rounded"><i class="uil-plus-circle"></i> Create New Product </a>
            </div>
            <form action="{{ route('admin.products.index') }}" method="GET" id="form">
                @csrf
                <div class="row mb-3">
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">Display:</span>
                        <select class="form-select mx-2" name="limit">
                            <option selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="my-auto">Products</span>
                    </div>
                    <div class="col-md-8"></div>
                    <div class="col-12 col-md-2 p-0 d-md-flex">
                        <label class="form-label my-auto me-md-2">Search: </label>
                        <input class="form-control" type="search" placeholder="Search" name="search" value="{{ request()->input('search') }}">
                    </div>
                </div>
            </form>
            <div class="overflow-auto">
                <table class="table">
                    <thead>
                    <tr>
                        <td>Product</td>
                        <td>SKU</td>
                        <td>Type</td>
                        <td>Supplier</td>
                        <td>Brand</td>
                        <td>Total Stock</td>
                        <td>Available Stock</td>
                        <td>Action</td>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->type }}</td>
                            <td>{{ $product->supplier->name }}</td>
                            <td>{{ $product->brand->name }}</td>
                            <td>{{ $product->total_stock }}</td>
                            <td>{{ $product->available_stock }}</td>
                            <td>
                                @can('edit-product')
                                    <a href="{{ route('admin.products.edit', [$product->getRouteKey()]) }}"><i class="fa fa-edit"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="text-center">There are no products found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2">
            {{ $products->links() }}
        </div>
    </div>
@endsection

