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
                @csrf
                <div class="row mb-3">
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">{{ __('Display') }}:</span>
                        <select class="form-select form-select-sm mx-2" name="limit">
                            <option selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="my-auto">{{ (__('rows')) }}</span>
                    </div>
                    <div class="col-md-3 d-flex">
                        <span class="my-auto">{{ __('Filter') }}</span>
                        <select name="filter" class="form-select form-select-sm rounded mx-2">
                            <option value="">{{ __('Choose') }}</option>
                        </select>
                    </div>
                    <div class="col-md-5"></div>
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
                                <td>{{ $$routeModel->status }}</td>
                                <td>{{ $$routeModel->grant_total }}</td>
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
