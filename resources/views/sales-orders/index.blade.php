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
            <div class="row mb-3">
                <a href="{{ route($routePrefix . '.' . $routeName . '.create') }}" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-primary rounded"><i class="uil-plus-circle"></i> Create New {{ $modelName }} </a>
            </div>
            <form action="{{ route($routePrefix . '.' . $routeName . '.index') }}" method="GET" id="form">
                @csrf
                <div class="row mb-3">
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">Display:</span>
                        <select class="form-select mx-2" name="limit">
                            <option selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="my-auto">{{ $modelName }}</span>
                    </div>
                    <div class="col-md-8"></div>
                    <div class="col-12 col-md-2 p-0 d-md-flex">
                        <label class="form-label my-auto me-md-2">Search: </label>
                        <input class="form-control" type="search" placeholder="Search" name="search" value="{{ request()->input('search') }}">
                    </div>
                </div>
            </form>
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
                        @forelse ($models as $$routeModel)
                            <tr>
                                @foreach ($model->indexFields as $indexField)
                                    @if (strtolower($indexField['columnName']) == 'show') 
                                        <td><a href="{{ route($routePrefix . '.' . $routeName . '.show', $$routeModel->getRouteKey()) }}"><i class="uil-eye"></i></a></td>
                                    @elseif (strtolower($indexField['columnName']) == 'edit') 
                                        <td><a href="{{ route($routePrefix . '.' . $routeName . '.edit', $$routeModel->getRouteKey()) }}"><i class="uil-edit"></i></a></td>
                                    @else
                                        @if (empty($$routeModel[$indexField['columnName']]))
                                            <td>-</td>
                                        @else
                                            @if (!empty($indexField['relation']))
                                                <td>{{ $$routeModel[$indexField['relation']]->name }}</td>
                                            @elseif (strtolower($indexField['columnName']) == 'status_text')
                                                <td>
                                                    <span class="{{ $$routeModel[$indexField['columnClass']] }}">
                                                        {{ $$routeModel[$indexField['columnName']] }}
                                                    </span>
                                                </td>
                                            @else
                                                <td>{{ $$routeModel[$indexField['columnName']] }}</td>
                                            @endif
                                        @endif
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
        <div class="mt-2">
            {{ $models->links() }}
        </div>
    </div>
@endsection