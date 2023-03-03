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
        <div class="bg-white card shadow py-3 px-4">
            <div class="d-flex flex-row justify-content-between mb-3">
                <a href="{{ route($routePrefix . '.' . $routeName . '.create') }}" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-primary rounded">{{ __('Create Delivery Order') }} </a>
                <button type="button" class="btn btn-sm btn-light col-md-1">{{ __('Print') }}</button>
            </div>
            <form action="{{ route($routePrefix . '.' . $routeName . '.index') }}" method="GET" id="form">
                <div class="row mb-3">
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">{{ __('Display') }}:</span>
                        <select class="form-select form-select-sm mx-2" name="limit">
                            <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50</option>
                        </select>
                        <span class="my-auto">{{ (__('rows')) }}</span>
                    </div>
                    <div class="col-md-3 d-flex">
                        <span class="my-auto">{{ __('Filter') }}</span>
                        <select name="filter" class="form-select form-select-sm rounded mx-2">
                            <option value="">{{ __('Choose') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4"></div>
                    <div class="col-12 col-md-2 p-0 d-md-flex">
                        <label class="form-label my-auto me-md-2">{{ __('Search') }}: </label>
                        <input class="form-control input-sm" type="search" placeholder="Search" name="search" value="{{ request()->input('search') }}">
                    </div>
                </div>
            </form>
            <div class="overflow-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input">
                            </td>
                            <td>{{ __('Delivery Order ID') }}</td>
                            <td>{{ __('Sales Order ID') }}</td>
                            <td>{{ __('Customer Name') }}</td>
                            <td>{{ __('Date') }}</td>
                            <td>{{ __('Status') }}</td>
                            <td>{{ __('Amount (SGD)') }}</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($models as $$routeModel)
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>DO{{ $$routeModel->id }}</td>
                                <td>SO{{ $$routeModel->salesOrder->id }}</td>
                                <td></td>
                                <td>{{ $$routeModel->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @if ($$routeModel->status == \App\Models\DeliveryOrder::STATUS_PENDING)
                                    <span class="badge bg-danger">{{ \App\Models\DeliveryOrder::STATUS_ARRAY[\App\Models\DeliveryOrder::STATUS_PENDING] }}</span>
                                    @elseif ($$routeModel->status == \App\Models\DeliveryOrder::STATUS_INVOICED)
                                    <span class="badge bg-primary">{{ \App\Models\DeliveryOrder::STATUS_ARRAY[\App\Models\DeliveryOrder::STATUS_INVOICED] }}</span>
                                    @elseif ($$routeModel->status == \App\Models\DeliveryOrder::STATUS_ONGOING)
                                    <span class="badge bg-warning">{{ \App\Models\DeliveryOrder::STATUS_ARRAY[\App\Models\DeliveryOrder::STATUS_ONGOING] }}</span>
                                    @elseif ($$routeModel->status == \App\Models\DeliveryOrder::STATUS_PACKED)
                                    <span class="badge bg-info">{{ \App\Models\DeliveryOrder::STATUS_ARRAY[\App\Models\DeliveryOrder::STATUS_PACKED] }}</span>
                                    @elseif ($$routeModel->status == \App\Models\DeliveryOrder::STATUS_SHIPPED)
                                    <span class="badge bg-primary">{{ \App\Models\DeliveryOrder::STATUS_ARRAY[\App\Models\DeliveryOrder::STATUS_SHIPPED] }}</span>
                                    @endif
                                </td>
                                <td>{{ $$routeModel->grant_total }}</td>
                                <td>
                                    <a href="{{ route($routePrefix . '.' . $routeName . '.show', ['delivery_order' => $$routeModel]) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                                </td>
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
        <div class="mt-2">
            {{ $models->links() }}
        </div>
    </div>
@endsection
