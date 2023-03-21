@extends('layouts/layout')

@section('content')
    <div class="container-fluid" id="app">
        <!-- title -->
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
            <span class="page-title h4">
                {{ __('Product Details') }}
            </span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Product List</a></li>
                    <li class="breadcrumb-item active">Product Details</li>
                </ol>
            </nav>
        </div>
        <div class="card shadow bg-white mt-2">
            <div class="body border-top">
                <ul class="nav nav-pills nav-tabs nav-fill m-3" id="myTab" role="tablist">
                    <div class="col-md-3 d-flex justify-content-center">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-3" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" aria-controls="overview" aria-selected="true">{{ __('Overview') }}</button>
                        </li>
                    </div>
                    <div class="col-md-3 d-flex justify-content-center">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3" id="transaction-tab" data-bs-toggle="tab" data-bs-target="#transaction" type="button"aria-controls="transaction" aria-selected="false">{{ __('Transaction') }}</button>
                        </li>
                    </div>
                    <div class="col-md-3 d-flex justify-content-center">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" aria-controls="products" aria-selected="false">{{ __('Related Products') }}</button>
                        </li>
                    </div>
                    <div class="col-md-3 d-flex justify-content-center">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" aria-controls="history" aria-selected="false">{{ __('History') }}</button>
                        </li>
                    </div>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                        @include('admin.products.show')
                    </div>
                    <div class="tab-pane fade" id="transaction" role="tabpanel" aria-labelledby="transaction-tab">
                        @include('admin.products.transaction')
                    </div>
                    <div class="tab-pane fade" id="products" role="tabpanel" aria-labelledby="products-tab">
                        @include('admin.products.products')
                    </div>
                    <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                        @include('admin.products.history')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .nav-pills .nav-item .nav-link {
        color: black;
        background-color: #DCDCDC;
    }
</style>
