@extends('layouts/layout')

@section('content')
<div class="container-fluid" id="app">
    <!-- title -->
    <div class="row justify-content-between align-items-center w-100 border-bottom pb-4">
        <div class="col-md-12">
            <span>
                <h5>{{ __('Product Details') }}</h5>
            </span>
        </div>
    </div>
    <div class="card shadow bg-white mt-4">
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
