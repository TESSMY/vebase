@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <span class="page-title h4">{{ $modelName }}</span>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <form action="{{ route($routePrefix . '.' . $routeName . '.generate') }}" method="POST">
            @csrf
            <div class="bg-white card shadow py-3 px-4">
                <div class="row mb-3">
                    <span class="h5">{{ $modelName }} Information</span>
                </div>
                <div class="row mb-3">
                    <div class="row mb-3">
                        <span class="h5">{{ $modelName }} Export</span>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <select class="form-select">
                            <option value="2">EXCEL</option>
                        </select>
                    </div>
                </div>
                <div class="row px-2">
                    <button type="submit" class="col-12 col-md-1 mb-3 mb-md-0 me-0 btn btn-success rounded">Generate</button>
                    <div class="col-md-10"></div>
                    <a href="{{ route('admin.inventory-reports.history') }}" class="col-12 col-md-1 mb-3 mb-md-0 btn btn-secondary rounded">History</a>
                </div>
            </div>
        </form>
        <form action="{{ route($routePrefix . '.' . $routeName . '.index') }}" method="GET">
            @csrf
            <div class="bg-white card shadow py-3 px-4">
                <div class="row mb-2">
                    <span class="h5">{{ $modelName }} Information</span>
                </div>
                <div class="row border-bottom pb-3">
                    <div class="col-12 col-md-2 mb-md-0 d-flex">
                        <span class="my-auto">Display:</span>
                        <select class="form-select mx-2" name="limit">
                            <option value="10" {{ $limit == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $limit == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $limit == '50' ? 'selected' : '' }}>50</option>
                        </select>
                        <span class="my-auto">entries</span>
                    </div>
                    <div class="col-md-8"></div>
                    <div class="col-12 col-md-2 p-0 d-md-flex">
                        <label class="form-label my-auto me-md-2">Search: </label>
                        <div class="input-group">
                            <input type="search" class="form-control" placeholder="Search" name="search" value="{{ request()->input('search') }}">
                            <button class="btn border" type="button"><i class="uil-search-alt"></i></button>
                        </div>
                    </div>
                </div>
                <div class="overflow-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Qty on hand</th>
                                <th>Qty on order</th>
                                <th>Qty pending back order</th>
                                <th>Qty back order</th>
                                <th>Free balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($productVariants as $productVariant)
                                <tr>
                                    <td>{{ $productVariant->name }}</td>
                                    <td>{{ $productVariant->sku }}</td>
                                    <td>{{ $productVariant->total_stock ?? 0 }}</td>
                                    <td>{{ $productVariant->qtyOnOrder ?? 0 }}</td>
                                    <td>{{ $productVariant->qtyPendingOrder ?? 0 }}</td>
                                    <td>{{ $productVariant->qtyBackOrder ?? 0 }}</td>
                                    <td>{{ $productVariant->total_stock + $productVariant->qtyOnOrder - $productVariant->qtyPendingOrder - $productVariant->qtyBackOrder }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">There are no {{ $modelName }} found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
        <div class="mt-2">
            {{ $productVariants->links() }}
        </div>
    </div>
@endsection