@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">View {{ $modelName }}</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.' . $routeName . '.index') }}">{{ $modelName }}</a></li>
                    <li class="breadcrumb-item active">View {{ $modelName }}</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        @if (View::exists($routePrefix . '.' . $routeName . '.show-body'))
            @include($routePrefix . '.' . $routeName . '.show-body')
        @else
            <div class="row">
                @if (View::exists($routePrefix . '.' . $routeName . '.show-information'))
                    @include($routePrefix . '.' . $routeName . '.show-information')
                @else
                    <div class="col-lg-6 col-md-7">
                        <div class="bg-white card shadow">
                            <div class="card-body">
                                <div class="row">
                                    <span class="h5">Information</span>
                                </div>
                                <div class="border mb-2"></div>
                                <div class="row mb-2">
                                    @if (!empty($model->showFields))
                                        @foreach ($model->showFields as $showField)
                                            @php
                                                $columnName = $showField['columnName'] ?? strtolower(Str::snake($showField['displayName']));
                                                $show = true;
                                                if (!empty($showField['permissions'])) {
                                                    foreach ($showField['permissions'] as $permission) {
                                                        if (empty($authUser) || !$authUser->can($permission)) {
                                                            $show = false;
                                                            break;
                                                        }
                                                    }
                                                }
                                                if (!empty($showField['roles'])) {
                                                    if (empty($authUser) || !$authUser->hasAnyRole(array_merge($showField['roles'], [\App\Models\User::ROLE_SUPER_ADMIN]))) {
                                                        $show = false;
                                                        break;
                                                    }
                                                }
                                                if (!$show) {
                                                    continue;
                                                }
                                            @endphp
                                            <span class="col-5 mb-2 fw-bold">{{ $showField['displayName'] }} </span>

                                            <span class="col-7 mb-2">
                                                @if (empty($$routeModel[$columnName]))
                                                    -
                                                @elseif (empty($indexField['type']))
                                                    {{ $$routeModel[$columnName] }}
                                                @elseif ($indexField['type'] == 'image')
                                                    <img src="{{ $$routeModel[$columnName] }}" class="{{ $$routeModel[$indexField['class']] ?? 'avatar' }}">
                                                @elseif ($indexField['type'] == 'boolean')
                                                    <td>{{ !empty($$routeModel[$columnName]) ? 'Yes' : 'No' }}</td>
                                                @elseif ($indexField['type'] == 'relation')
                                                    @php
                                                        $relation = explode('.', $indexField['relation']);
                                                        $data = $$routeModel;
                                                        for ($i = 0; $i < count($relation); $i++) {
                                                            $data = $data?->{$relation[$i]};
                                                        }
                                                    @endphp
                                                    {{ $data?->{$indexField['relatedColumnName']} ?? '-' }}
                                                @elseif ($indexField['type'] == 'html')
                                                    {!! $indexField['html'] !!}
                                                @elseif ($indexField['type'] == 'decimal')
                                                    {{ number_format($$routeModel[$columnName], $indexField['decimal']) }}
                                                @elseif ($indexField['type'] == 'url')
                                                    @php
                                                        $target = $indexField['target'] ?? '_blank';
                                                    @endphp
                                                    <a href="{{ $url }}" target="{{ $target }}">
                                                        {{ $indexField['displayText'] ?? 'View' }}
                                                    </a>
                                                @elseif ($indexField['type'] == 'decimal_with_currency')
                                                    <td>{{ $$routeModel[$columnName] . ' ' . $indexField['currency'] }}</td>
                                                @elseif ($indexField['type'] == 'dollar_decimal')
                                                    <td>$ {{ number_format($$routeModel[$columnName], $indexField['decimal']) }}</td>
                                                @endif
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        @can('update', $$routeModel)
                                            <a href="{{ route($routePrefix . '.' . $routeName . '.edit', $$routeModel->getRouteKey()) }}" class="btn btn-success me-3">Edit</a>
                                        @endcan
                                        <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="btn btn-dark me-3">Back</a>
                                    </div>
                                    @can('delete', $$routeModel)
                                        <form action="{{ route($routePrefix . '.' . $routeName . '.destroy', $$routeModel->getRouteKey()) }}" method="POST" enctype="multipart/form-data">
                                            @method('DELETE')
                                            @csrf
                                            <button class="btn btn-danger px-2" type="submit" onclick="return confirm('Are you sure you want to delete? You cannot revert this.')">
                                                Delete
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-5">
                        @if (View::exists($routePrefix . '.' . $routeName . '.show-right-box'))
                            @include($routePrefix . '.' . $routeName . '.show-right-box')
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
