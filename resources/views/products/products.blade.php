<div class="table-responsive">
    <table class="table table-rounded table-striped shadow w-100 mt-4">
        <thead>
            <tr class="text-left font-bold">
                <th class="pb-4 pt-6 px-6">{{ __('Product') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Sku') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Supplier') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Brand') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Piece Per Carton') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Total Stock') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Available Stock') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Sales Order Stock') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Retail Selling Price') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Unit Cost') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Lead Time') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Safety Stock') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Updated at') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Status') }}</th>
                <th class="pb-4 pt-6 px-6">{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="d-inline-flex">
                        <img src="" width="50" height="50" />
                        <span class="py-3 px-2 ">{{ __('Adirondack Chair') }}</span>
                    </div>
                </td>
                <td>{{ __('SKU') }} #12345</td>
                <td>{{ __('Supplier') }} #12345</td>
                <td>{{ __('Brand Name') }}</td>
                <td>15</td>
                <td>150</td>
                <td>150</td>
                <td>150</td>
                <td>$45.20</td>
                <td>$36.20</td>
                <td>3 {{ __('Days') }}</td>
                <td>50</td>
                <td>10/02/2023 15:35 PM</td>
                <td><button class="btn btn-success btn-sm w-100">{{ __s('Active') }}</button></td>
                <td><a href="#" class="btn btn-alt px-5">{{ __('View') }}</a></PrimaryButton></td>
            </tr>
        </tbody>
    </table>
</div>