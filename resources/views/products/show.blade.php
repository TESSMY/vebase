<div class="row mt-5 mx-3 my-3">
    <div class="col-md-5">
        <div>
            <img src="g" width="500" height="500" />
        </div>
    </div>
    <div class="col-md-7">
        <div class="d-flex">
            <div class="col-md-8 pe-6 col-12">
                <h3>{{ $product->name  }}</h3>
                <small class="fw-light">{{ __('Added Date') }}: {{ $product->created_at }}.</small>
            </div>
            <div class="col-md-4 col-12">
                <div>
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-secondary px-5">{{ __('Edit Product') }}</a>
                </div>
            </div>
        </div>
        <div class="d-flex mt-3">
            <div class="col-md-6 pe-6">
                <div class="fw-bold">{{ __('Unit Cost') }}:</div>
                <span>$ {{ $product->cost_price }}</span>
            </div>
            <div class="col-md-6">
                <div class="fw-bold">{{ __('Retail Price') }}:</div>
                <span>$ {{ $product->selling_price }}</span>
            </div>
        </div>

        <div class="mt-3">
            <div class="fw-bold">{{ __('Description') }}:</div>
            <span>{{ $product->description }}</span>
        </div>

        <div class="d-flex mt-3">
            <div class="col-md-3 pe-6">
                <div class="fw-bold">{{ __('SKU') }}:</div>
                <span>{{ __('SKU') }} #{{ $product->sku }}</span>
            </div>
            <div class="col-md-3 pe-6">
                <div class="fw-bold">{{ __('Barcode') }}:</div>
                <span>#{{ $product->barcode }}</span>
            </div>
            <div class="col-md-3 pe-6">
                <div class="fw-bold">{{ __('Available Stock') }}:</div>
                <span>{{ $product->available_stock }}</span>
            </div>
            <div class="col-md-3 pe-6">
                <div class="fw-bold">{{ __('Supplier') }}:</div>
                <span>{{ $product->supplier->name }}</span>
            </div>
        </div>
    </div>
</div>

