<div class="table-responsive mx-3 my-3">
    <table class="table table-rounded table-striped shadow w-100 mt-4">
        <thead>
            <tr class="text-left font-bold">
                <th class="pb-4 pt-6 px-6">{{ __('Product') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Sku') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Supplier') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Brand') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Selling Price') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Total Stock') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Available Stock') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($product->bundles as $bundle)
            <tr>
                <td>
                    <div class="d-inline-flex">
                        <img src="{{ $bundle->image }}" width="50" height="50" />
                        <span>{{ $bundle->productVariant->name }}</span>
                    </div>
                </td>
                <td>{{ __('SKU') }} #{{ $bundle->productVariant->sku }}</td>
                <td>{{ $bundle->productVariant->product->supplier->name }}</td>
                <td>{{ $bundle->productVariant->product->brand->name }}</td>
                <td>{{ $bundle->productVariant->selling_price }}</td>
                <td>{{ $bundle->productVariant->total_stock }}</td>
                <td>{{ $bundle->productVariant->available_stock }}</td>
                <td>
                    @can('view-product')
                        <a type="button" class="btn btn-primary px-2" href="{{ route('admin.products.show', [$product->getRouteKey()]) }}">View</a>
                    @endcan
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center bg-white">This product does not have any related products.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
