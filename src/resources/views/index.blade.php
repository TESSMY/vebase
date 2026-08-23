@extends('layouts/layout')

@section('content')
    @php
        /*
         * Resolved once. The import and export buttons each used to run their own
         * `Permission::where(...)->exists()` query -- three DB round trips per page render,
         * asking whether a permission row exists when the Gate check that follows already
         * answers that. Gating on the model's own import/export config instead also keeps
         * route() from being called for a route that was never registered.
         */
        $slug = \Illuminate\Support\Str::kebab(strtolower($modelName));
        $viewPrefix = $routePrefix . '.' . $routeName;
        $authUser = auth()->user();

        $canImport = ! empty($model->importExport) && ! $model->disableImport && $authUser?->can('import-' . $slug);
        $canExport = ! empty($model->importExport) && ! $model->disableExport && $authUser?->can('export-' . $slug);

        $searchable = $model->searchable;
        $withTrashed = $model::$resourceWithTrashed && in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model));
    @endphp
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
                @if ($canImport)
                    {{-- File input is submitted via fetch() (see veImportSubmit below)
                         instead of a native form submit, which a theme-level submit
                         handler was silently swallowing (page reloaded, no POST). --}}
                    <input name="import_file" type="file" accept=".xlsx, .csv" style="display: none" id="file-upload"
                           data-import-url="{{ route($viewPrefix . '.import') }}"
                           onchange="veImportSubmit(this)" />
                    <script>
                        function veImportSubmit(input) {
                            if (!input.files || !input.files.length) return;
                            var fd = new FormData();
                            fd.append('import_file', input.files[0]);
                            fd.append('_token', '{{ csrf_token() }}');
                            fetch(input.getAttribute('data-import-url'), {
                                method: 'POST',
                                body: fd,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                credentials: 'same-origin'
                            }).then(function (response) {
                                // Reloading unconditionally made a rejected upload (403, or a
                                // validation failure on type or size) look like a successful one.
                                if (!response.ok) {
                                    alert('Import failed (' + response.status + '). Please check the file and try again.');
                                    return;
                                }
                                window.location.reload();
                            }).catch(function (e) {
                                alert('Import failed: ' + e);
                            }).finally(function () {
                                input.value = '';
                            });
                        }
                    </script>
                @endif
                @if (View::exists($viewPrefix . '.index-header'))
                    @include($viewPrefix . '.index-header')
                @else
                    <div class="d-flex">
                        @can('create-' . $slug)
                            <a href="{{ route($viewPrefix . '.create') }}" class="btn btn-primary rounded me-2"><i class="uil-plus-circle"></i> Create </a>
                        @endcan
                        @if ($canImport)
                            <button class="btn btn-info rounded me-2" type="button" onclick="document.getElementById('file-upload').click()"><i class="uil-plus-circle"></i>  Import</button>
                        @endif
                        @if ($canExport)
                            <a class="btn btn-outline-info rounded me-2" href="{{ route($viewPrefix . '.export') }}"><i class="uil-export"></i>  Export</a>
                        @endif
                    </div>
                @endif
                <div class="row my-2">
                    <form action="{{ route($viewPrefix . '.index') }}" class="d-md-flex flex-wrap" method="GET">
                        @if (View::exists($viewPrefix . '.index-search'))
                            @include($viewPrefix . '.index-search')
                        @else
                            <div class="col-12 d-flex flex-wrap">
                                @if(! empty($searchable))
                                    <div class="col-12 col-md-6">
                                        <input class="form-control" type="search" name="search" placeholder="Search" maxlength="{{ \Vecapital\Vebase\VeHelper::MAX_SEARCH_LENGTH }}" value="{{ request()->get('search') }}">
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
                                                <option value="100" {{ $limit == '100' ? 'selected' : '' }}>100</option>
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
    @if (View::exists($viewPrefix . '.index-after'))
        @include($viewPrefix . '.index-after')
    @endif
@endsection
