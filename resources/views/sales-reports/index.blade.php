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
                        <input class="form-control" type="date" name="date_from">
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <label class="form-label">Date (To)</label>
                        <input class="form-control" type="date" name="date_to">
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <label class="form-label">Ignore Zero Value?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="true" id="zero_value">
                            <label class="form-check-label" for="zero_value">
                                True
                            </label>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <label class="form-label">Sort By Total Amount?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="true" id="sort_total_amount">
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
                            <option value="1">PDF</option>
                            <option value="2">EXCEL</option>
                        </select>
                    </div>
                </div>
                <div class="row px-2">
                    <a href="" class="col-12 col-md-1 mb-3 mb-md-0 me-0 me-md-3 btn btn-success rounded">Generate</a>
                    <a href="" class="col-12 col-md-1 mb-3 mb-md-0 btn btn-primary rounded">Download</a>
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
                            <option selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
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
                                @foreach ($model->indexFields as $indexField)
                                    <th>{{ $indexField['displayName'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesReports as $salesReport)
                                <tr>
                                    @foreach ($model->indexFields as $indexField)
                                        @if (strtolower($indexField['columnName']) == 'show') 
                                            <td><a href="{{ route($routePrefix . '.' . $routeName . '.show', $salesReport->getRouteKey()) }}"><i class="uil-eye"></i></a></td>
                                        @elseif (strtolower($indexField['columnName']) == 'edit') 
                                            <td><a href="{{ route($routePrefix . '.' . $routeName . '.edit', $salesReport->getRouteKey()) }}"><i class="uil-edit"></i></a></td>
                                        @else
                                            <td>{{ $salesReport[$indexField['columnName']] }}</td>
                                        @endif
                                    @endforeach
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