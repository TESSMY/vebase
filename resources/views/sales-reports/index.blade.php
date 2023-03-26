@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <span class="page-title h4">{{ $modelName }}</span>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <form action="{{ route($routePrefix . '.' . $routeName . '.index') }}" method="GET" id="form">
            @csrf
            <div class="bg-white card shadow py-3 px-4">
                <div class="row mb-3">
                    <span class="h5">{{ $modelName }} Information</span>
                </div>
                <div class="row mb-3">
                    <div class="col-12 col-md-6 mb-2">
                        <label class="form-label">Date (From)</label>
                        <input class="form-control" type="date" name="date_from" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <label class="form-label">Date (To)</label>
                        <input class="form-control" type="date" name="date_to" value="{{ $dateTo }}">
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <label class="form-label">Ignore Zero Value?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="true" name="zero_value" id="zero_value" {{ !empty($zeroValue) ? 'checked' : '' }}>
                            <label class="form-check-label" for="zero_value">
                                True
                            </label>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <label class="form-label">Sort By Total Amount?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="true" name="sort_total_amount" id="sort_total_amount">
                            <label class="form-check-label" for="sort_total_amount">
                                True
                            </label>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <span class="h5">{{ $modelName }} Export</span>
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <select class="form-select">
                            <option value="2">EXCEL</option>
                        </select>
                    </div>
                </div>
                <div class="row px-2">
                    <button type="submit" class="col-12 col-md-1 mb-3 mb-md-0 me-0 me-md-3 btn btn-success rounded">Generate</button>
                    <a href="{{ route('admin.sales-reports.export') }}" class="col-12 col-md-1 mb-3 mb-md-0 btn btn-primary rounded">Download</a>
                </div>
            </div>
            <div class="bg-white card shadow py-3 px-4">
                <div class="row mb-2">
                    <span class="h5">{{ $modelName }} Information</span>
                </div>
                <div class="row border-bottom pb-3">
                    <div class="col-12 col-md-2 mb-md-0 d-flex">
                        <span class="my-auto">Display:</span>
                        <select class="form-select mx-2" name="limit">
                            <option value="10" {{ $limit == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $limit == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $limit == '50' ? 'selected' : '' }}>50</option>
                        </select>
                        <span class="my-auto">entries</span>
                    </div>
                    <div class="col-md-8"></div>
                    <div class="col-12 col-md-2 p-0 d-md-flex">
                        <label class="form-label my-auto me-md-2">Search: </label>
                        <div class="input-group">
                            <input type="search" class="form-control" placeholder="Search" name="search" value="{{ request()->input('search') }}">
                            <button class="btn btn-outline-secondary" type="button"><i class="uil-search-alt"></i></button>
                        </div>
                    </div>
                </div>
                <div class="overflow-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Total Sales Orders</th>
                                <th>Total Cost</th>
                                <th>Total Profit</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesReports as $salesReport)
                                <tr>
                                    <td>
                                        @if (!empty($salesReport->client->name))
                                            {{ $salesReport->client->name }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $salesReport->total_sales_order }}</td>
                                    <td>$ {{ $salesReport->total_cost }}</td>
                                    <td>$ {{ $salesReport->total_profit }}</td>
                                    <td>$ {{ $salesReport->total_revenue }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">There are no {{ $modelName }} found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
        <div class="mt-2">
            {{ $salesReports->links() }}
        </div>
    </div>
@endsection