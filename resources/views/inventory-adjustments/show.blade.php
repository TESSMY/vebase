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
        <div class="bg-white card shadow py-3 px-4">
            <div class="row border-bottom mb-2">
                <span class="h5">Adjustment Information</span>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Adjustment Type</label>
                    <input class="form-control" type="text" value="Quantity Adjustment" placeholder="Adjustment Type" disabled>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Reference</label>
                    <input class="form-control" type="text" name="reference" placeholder="Reference" value="{{ $inventoryAdjustment->reference }}" disabled>
                </div>
                <div class="col-12 col-md-6 mb-md-0 mb-2">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="5" style="resize: none;" disabled>{{ $inventoryAdjustment->desription }}</textarea>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="form-label">Reason</label>
                    <input class="form-control" type="text" name="reason" placeholder="reason" value="{{ $inventoryAdjustment->reason }}" disabled>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row mb-2">
                <span class="h4">Product Details</span>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Available Stock</th>
                        <th>New Stock Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventoryAdjustment->adjustmentItems as $adjustmentItem)
                        <tr>
                            <td>
                                <input class="form-control" type="text" value="{{ $adjustmentItem->product->name }}" disabled>
                            </td>
                            <td>
                                <input class="form-control" type="text" value="{{ $adjustmentItem->old_value }}" disabled>
                            </td>
                            <td>
                                <input class="form-control" type="text" value="{{ $adjustmentItem->new_value }}" disabled>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="text-center">There are no {{ $modelName }} found.</td>
                        </tr>
                    @endforelse
                    
                </tbody>
            </table>
            <div class="row col-12">
                <a href="/admin/inventory-adjustments" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
            </div>
        </div>
    </div>
@endsection
