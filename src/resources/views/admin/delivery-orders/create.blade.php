<div class="row">
    <div class="col-md-12 pb-3">
        <h5 class="fw-bold">{{ __('DELIVERY ORDER DETAILS') }}</h5>
    </div>
</div>

<div class="row pb-2">
    <div class="row">
        <div class="col-md-6">{{ __('Client') }}</div>
        <div class="col-md-6">{{ __('Date') }}</div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <select name="customer_id" id="" class="form-control">
                <option value="">{{ __('Select Client') }}</option>
            </select>
        </div>
        <div class="col-md-6">
            <input type="date" class="form-control" />
        </div>
    </div>
</div>

<div class="row pb-2">
    <div class="row">
        <div class="col-md-6">{{ __('Customer P.O') }}</div>
        <div class="col-md-6">{{ __('Payment Term') }}</div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <input type="text" placeholder="Enter Customer P.O" class="form-control" />
        </div>
        <div class="col-md-6">
            <input type="text" placeholder="0" class="form-control" />
        </div>
    </div>
</div>

<div class="row pb-2">
    <div class="row">
        <div class="col-md-6">{{ __('Branch Code') }}</div>
        <div class="col-md-6">{{ __('Payment Due') }}</div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <input type="text" disabled class="form-control" />
        </div>
        <div class="col-md-6">
            <input type="date" disabled class="form-control" />
        </div>
    </div>
</div>

<div class="row pb-2">
    <div class="row">
        <div class="col-md-6">{{ __('Issued By') }}</div>
        <div class="col-md-6">{{ __('Packed By Date') }}</div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <input type="text" placeholder="Admin" disabled class="form-control" />
        </div>
        <div class="col-md-6">
            <input type="date" class="form-control" />
        </div>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-12 pb-3">
        <h5 class="fw-bolder">{{ __('ADD PRODUCT') }}</h5>
    </div>
</div>

<div class="card-styles">
    <div class="card-style-3 mb-30">
        <div class="card-content">
            <div class="table-responsive">
                <table class="min-w-full table shadow-sm rounded p-3 admin-merchants-table">
                    <thead>
                        <tr class="bg-table-dark">
                            <th>{{ __('Product Details') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Quantity') }}</th>
                            <th>{{ __('Unit Price') }}</th>
                            <th>{{ __('Taxes') }}</th>
                            <th>{{ __('Total Amount') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- data here -->
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2 text-end">
                <!-- pagination here -->
            </div>
        </div>
    </div>
</div>