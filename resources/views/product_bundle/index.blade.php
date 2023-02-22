<div class="container-fluid" id="app">

    @if(session()->has('message'))
    <div class="alert alert-danger">
        {{ session()->get('message') }}
    </div>
    @endif

    {{-- header --}}
    <div class="row justify-content-between align-items-center w-100 border-bottom pb-4">
        <div class="col-md-12">
            <span>
                <h5><i class="fa fa-store text-identity me-4" style="color: #289983"></i>{{ __('Product Bundling') }}</h5>
            </span>
        </div>
    </div>
    {{-- search and button --}}
    <div class="row my-4">
        <div class="d-flex justify-content-between align-items-end">
            <div class="col-12 col-md-auto d-flex">
                <a href="{{ route('admin.ve-product-bundles.create') }}" class="btn btn-primary px-5">{{ __('New Bundle') }}</a>
            </div>
            <div class="col-lg-4 col-md-6">
                <form id="frmSearch" action="{{ route('admin.ve-product-bundles.index') }}" method="GET">
                    @csrf
                    <div class="filter">
                        <div class="d-flex justify-content-between align-items-center filter-search px-2 py-2">
                            <input type="text" name="q" placeholder="Search in the current filter" value="{{ $query }}" class="w-100">
                            <i class="bi bi-search" onclick="document.getElementById('frmSearch').submit()" style="cursor: pointer;"></i>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- index table --}}
    <div class="row mt-2">
        <div class="table-responsive">
            <table class="table table-rounded table-striped shadow w-100 mt-4">
                <thead>
                    <tr>
                        <th class="px-5" style="width:25%">{{ __('Product Bundle') }}</th>
                        <th style="width:15%">{{ __('Sku') }}</th>
                        <th style="width:15%">{{ __('Bundle Barcode') }}</th>
                        <th style="width:10%">{{ __('Total Stock') }}</th>
                        <th style="width:10%">{{ __('Available Stock') }}</th>
                        <th style="width:15%">{{ __('Bundle Cost') }}</th>
                        <th style="width:15%">{{ __('Bundle Selling Price') }}</th>
                        <th style="width:5%">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="d-inline-flex">
                                <img src="https://larmoire.vecapital.asia/storage/products/images/0117202317350863c66bcca4bb2.jpeg" width="50" height="50" alt="Article thumbnail" />
                                <span class="py-3 px-2">Tea Table Set</span>
                            </div>
                        </td>
                        <td>{{ __('SKU') }} #12345</td>
                        <td>NP #12345</td>
                        <td>150</td>
                        <td>150</td>
                        <td>$25.90</td>
                        <td>$45.90</td>
                        <td><a href="{{ route('admin.ve-products.edit', 1) }}" class="btn btn-primary px-5">{{ __('Edit') }}</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
