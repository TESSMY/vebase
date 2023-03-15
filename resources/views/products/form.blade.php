@extends('layouts/layout')
@section('content')
    <div class="container-fluid" id="app">
        {{-- header --}}
        <div class="d-flex justify-content-between p-3" style="border-bottom-width:2px">
            <div>
                <span>
                    <h4><i class="fas fa-store text-identity me-2" style="color: #289983"></i>
                        @if(isset($product))
                            {{ __('Edit Product') }}
                        @else
                            {{ __('Create Product') }}
                        @endif
                    </h4>
                </span>
            </div>
            <div class="mt-2" style="border-top-width:3px">
                <div>
                    <a href="#" class="btn btn-alt px-5">{{ __('Back') }}</a>
                </div>
            </div>
        </div>

        {{-- form --}}
        @if(isset($product))
            <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @method('PATCH')
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
    </div>
@endsection

