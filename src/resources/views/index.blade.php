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
        <div class="bg-white card shadow">
            <div class="card-body">
                <form method="POST" action="{{ route($routePrefix . '.' . $routeName . '.import') }}" enctype="multipart/form-data">
                    @csrf
                    <input name="import_file" type="file" accept=".xlsx, .csv" style="display: none" id="file-upload" onchange="this.form.submit()" />
                </form>
                @if (View::exists($routePrefix . '.' . $routeName . '.index-header'))
                    @include($routePrefix . '.' . $routeName . '.index-header')
                @else
                    <div class="d-flex">
                        @can('create-' . strtolower($modelName))
                            <a href="{{ route($routePrefix . '.' . $routeName . '.create') }}" class="btn btn-primary rounded me-2"><i class="uil-plus-circle"></i> Create </a>
                        @endcan
                        @can('import-' . strtolower($modelName))
                            <button class="btn btn-info rounded me-2" type="button" onclick="$('#file-upload').click()"><i class="uil-plus-circle"></i>  Import</button>
                        @endcan
                        @can('export-' . strtolower($modelName))
                            <a class="btn btn-outline-info rounded me-2" href="{{ route($routePrefix . '.' . $routeName . '.export') }}"><i class="uil-export"></i>  Export</a>
                        @endcan
                    </div>
                @endif
                <div class="row my-3">
                    <form action="{{  route($routePrefix . '.' . $routeName . '.index') }}" class="d-md-flex" method="GET">
                        @if (View::exists($routePrefix . '.' . $routeName . '.index-search'))
                            @include($routePrefix . '.' . $routeName . '.index-search')
                        @else
                            <div class="col-md-6">
                                <input class="form-control" type="search" name="search" placeholder="Search" value="{{ request()->get('search') }}">
                            </div>
                            <div class="col-md-6 mt-2 mt-md-0">
                                <div class="row justify-content-md-end">
                                    <div class="col-auto">
                                        <label class="col-form-label">Display:</label>
                                    </div>
                                    <div class="col-auto">
                                        <select class="form-select" name="limit" onchange="this.form.submit()">
                                            <option value="10" {{ empty(request()->input('limit')) || request()->input('limit') == '10' ? 'selected' : '' }}>10</option>
                                            <option value="25" {{ request()->input('limit') == '25' ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request()->input('limit') == '50' ? 'selected' : '' }}>50</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="overflow-auto">
                    @include('vebase::common.table')
                </div>
            </div>
        </div>
        <div class="mt-2">
            @include('vebase::common.pagination')
        </div>
    </div>
    @if (View::exists($routePrefix . '.' . $routeName . '.index-after'))
        @include($routePrefix . '.' . $routeName . '.index-after')
    @endif
@endsection