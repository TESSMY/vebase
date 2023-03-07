@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Add New {{ $modelName }}</span>
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
            <form action="{{ route($routePrefix . '.' . $routeName . '.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row border-bottom mb-2">
                    <span class="h5">{{ $modelName }} Information</span>
                </div>
                @include('admin.invoices.form')
                <div class="row col-12">
                    <button type="submit" class="col-12 col-md-1 btn btn-success m-2">Create</button>
                    <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
                </div>
            </form>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row mb-2">
                <span class="h4">ADD PRODUCT</span>
            </div>
            <div class="overflow-auto">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Product Details</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Product Name</td>
                            <td>
                                <input class="form-control" type="number" min="0" required>
                            </td>
                            <td>Otto</td>
                            <td>Otto</td>
                            <td>
                                <span class="btn">
                                    <i class="uil-trash" style="color: red"></i>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="row container-fluid">
                <div class="col-12 col-md-4 mb-md-0 mb-3">
                    <span>Add another line</span>
                    <span>Import Product List</span>
                    <div class="row px-0">
                        <label class="form-label px-0">Notes and instructions</label>
                        <textarea class="form-control" placeholder="Notes and instructions" rows="5" style="resize: none"></textarea>
                    </div>
                </div>
                <div class="col-md-5"></div>
                <div class="col-12 col-md-3">
                    <div class="row text-end">
                        <span class="col-8 fw-bold">Sub Total: </span>
                        <span class="col-4">100</span>
                        <div class="border my-2"></div>
                        <span class="col-8 fw-bold">Tax: </span>
                        <span class="col-4">15%</span>
                        <div class="border my-2"></div>
                        <span class="col-8 fw-bold">Total (SGD): </span>
                        <span class="col-4">100</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
