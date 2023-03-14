@extends('layouts/layout')
@section('content')
    <div class="container-fluid" id="app">
        {{-- header --}}
        <div class="d-flex justify-content-between p-3" style="border-bottom-width:2px">
            <div>
                <span>
                    <h4><i class="fas fa-store text-identity me-2" style="color: #289983"></i>{{ __('Create Product') }}</h4>
                </span>
            </div>
            <div class="mt-2" style="border-top-width:3px">
                <div>
                    <a href="#" class="btn btn-alt px-5">{{ __('Back') }}</a>
                </div>
            </div>
        </div>

        {{-- form --}}
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <product-form :suppliers="{{ $suppliers }}" :products="{{ $products }}" :brands="{{ $brands }}"></product-form>
            <div class="mt-3">
                <button class="btn btn-primary me-2" type="submit">{{ __('Create') }}</button>
                <button class="btn btn-secondary">{{ __('Close') }}</button>
            </div>
        </form>
    </div>
@endsection

