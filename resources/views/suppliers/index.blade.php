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
                <div class="col-md-8">
                    <a href="{{ route($routePrefix . '.' . $routeName . '.create') }}" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-primary rounded"><i class="uil-plus-circle"></i> Create New {{ $modelName }} </a>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-secondary rounded ms-1" data-bs-toggle="modal" data-bs-target="#importModal">Import</button>
                    <form action="{{ route('admin.suppliers.export') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <button type="submit" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-secondary rounded my-1">Export</button>
                    </form>
                </div>
            </div>

            <!-- Modal -->
            <form action="{{ route('admin.suppliers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h1 class="modal-title fs-5" id="importModalLabel">Import Suppliers</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="file" name="import_file" class="form-control" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                    </div>
                </div>
            </form>

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
