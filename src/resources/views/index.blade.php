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
                @if(Spatie\Permission\Models\Permission::where('name', 'import-' . strtolower(\Illuminate\Support\Str::kebab($modelName)))->exists())
                    @can('import-' . strtolower(\Illuminate\Support\Str::kebab($modelName)))
                        <form method="POST" action="{{ route($routePrefix . '.' . $routeName . '.import') }}" enctype="multipart/form-data">
                            @csrf
                            <input name="import_file" type="file" accept=".xlsx, .csv" style="display: none" id="file-upload" onchange="this.form.submit()" />
                        </form>
                    @endcan
                @endif
                @if (View::exists($routePrefix . '.' . $routeName . '.index-header'))
                    @include($routePrefix . '.' . $routeName . '.index-header')
                @else
                    <div class="d-flex">
                        @can('create-' . strtolower(\Illuminate\Support\Str::kebab($modelName)))
                            <a href="{{ route($routePrefix . '.' . $routeName . '.create') }}" class="btn btn-primary rounded me-2"><i class="uil-plus-circle"></i> Create </a>
                        @endcan
                        @if(Spatie\Permission\Models\Permission::where('name', 'import-' . \Illuminate\Support\Str::kebab(strtolower($modelName)))->exists())
                            @can('import-' . strtolower(\Illuminate\Support\Str::kebab($modelName)))
                                <button class="btn btn-info rounded me-2" type="button" onclick="$('#file-upload').click()"><i class="uil-plus-circle"></i>  Import</button>
                            @endcan
                        @endif
                        @if(Spatie\Permission\Models\Permission::where('name', 'export-' . \Illuminate\Support\Str::kebab(strtolower($modelName)))->exists())
                            @can('export-' . strtolower(\Illuminate\Support\Str::kebab($modelName)))
                                <a class="btn btn-outline-info rounded me-2" href="{{ route($routePrefix . '.' . $routeName . '.export') }}"><i class="uil-export"></i>  Export</a>
                            @endcan
                        @endif
                    </div>
                @endif
                <div class="row my-2">
                    <form action="{{  route($routePrefix . '.' . $routeName . '.index') }}" class="d-md-flex flex-wrap" method="GET">
                        @if (View::exists($routePrefix . '.' . $routeName . '.index-search'))
                            @include($routePrefix . '.' . $routeName . '.index-search')
                        @else
                            <div class="col-12 d-flex flex-wrap">
                                @php
                                    $searchable = $model->searchable;
                                    $withTrashed = $model::$resourceWithTrashed && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model));
                                @endphp
                                @if(! empty($searchable))
                                    <div class="col-12 col-md-6">
                                        <input class="form-control" type="search" name="search" placeholder="Search" value="{{ request()->get('search') }}">
                                    </div>
                                @endif
                                @if ($withTrashed)
                                    <div class="col-12 col-md-3 mt-2 mt-md-0">
                                        <div class="row @if (!empty($searchable)) justify-content-md-end @endif">
                                            <div class="col-auto">
                                                <label class="col-form-label">Include Trashed:</label>
                                            </div>
                                            <div class="col-auto">
                                                <select class="form-select" name="trashed" onchange="this.form.submit()">
                                                    <option value="0" {{ empty(request()->input('trashed')) || request()->input('trashed') == '0' ? 'selected' : '' }}>No</option>
                                                    <option value="1" {{ request()->input('trashed') == '1' ? 'selected' : '' }}>Yes</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-12 mt-2 mt-md-0 @if (!empty($searchable) || $withTrashed) col-md-6 @else col-md-3 @endif">
                                    <div class="row @if (!empty($searchable) || $withTrashed) justify-content-md-end @endif ml-6">
                                        <div class="col-auto">
                                            <label class="col-form-label">Display:</label>
                                        </div>
                                        <div class="col-auto">
                                            <select class="form-select" name="limit" onchange="this.form.submit()">
                                                <option value="10" {{ $limit == '10' ? 'selected' : '' }}>10</option>
                                                <option value="25" {{ $limit == '25' ? 'selected' : '' }}>25</option>
                                                <option value="50" {{ $limit == '50' ? 'selected' : '' }}>50</option>
                                                <option value="50" {{ $limit == '100' ? 'selected' : '' }}>100</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @includeIf('vebase::index-filter')
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