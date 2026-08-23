@extends('layouts/layout')

@section('content')

    @php
        // $authUser was never defined here, so every `permissions`-gated show field evaluated
        // its guard against an undefined variable and was silently hidden.
        $authUser = auth()->user();
        $viewPrefix = $routePrefix . '.' . $routeName;
        $superAdminRole = defined('\App\Models\User::ROLE_SUPER_ADMIN') ? [\App\Models\User::ROLE_SUPER_ADMIN] : [];
    @endphp

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">View {{ $modelName }}</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route($viewPrefix . '.index') }}">{{ $modelName }}</a></li>
                    <li class="breadcrumb-item active">View {{ $modelName }}</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        @if (View::exists($viewPrefix . '.show-body'))
            @include($viewPrefix . '.show-body')
        @else
            <div class="row">
                @if (View::exists($viewPrefix . '.show-information'))
                    @include($viewPrefix . '.show-information')
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
                                                foreach ($showField['permissions'] ?? [] as $permission) {
                                                    if (empty($authUser) || !$authUser->can($permission)) {
                                                        $show = false;
                                                        break;
                                                    }
                                                }
                                                if ($show && !empty($showField['roles'])) {
                                                    // Was a `break`, which escaped the showFields loop and
                                                    // dropped every remaining field, not just this one.
                                                    $show = !empty($authUser) && $authUser->hasAnyRole(array_merge($showField['roles'], $superAdminRole));
                                                }
                                                if (!$show) {
                                                    continue;
                                                }

                                                $type = $showField['type'] ?? null;
                                            @endphp
                                            <span class="col-5 mb-2 fw-bold">{{ $showField['displayName'] }} </span>

                                            <span class="col-7 mb-2">
                                                {{-- Every branch below used to read $indexField, which does not
                                                     exist in this view -- so `type` was always seen as empty and
                                                     no field ever rendered as anything but plain text. --}}
                                                @if (empty($$routeModel[$columnName]) && $type !== 'boolean')
                                                    -
                                                @elseif (empty($type))
                                                    {{ $$routeModel[$columnName] }}
                                                @elseif ($type == 'image')
                                                    <img src="{{ $$routeModel[$columnName] }}" class="{{ $showField['class'] ?? 'avatar' }}">
                                                @elseif ($type == 'boolean')
                                                    {{ !empty($$routeModel[$columnName]) ? 'Yes' : 'No' }}
                                                @elseif ($type == 'relation')
                                                    @php
                                                        $relation = explode('.', $showField['relation']);
                                                        $data = $$routeModel;
                                                        for ($i = 0; $i < count($relation); $i++) {
                                                            $data = $data?->{$relation[$i]};
                                                        }
                                                    @endphp
                                                    {{ $data?->{$showField['relatedColumnName']} ?? '-' }}
                                                @elseif ($type == 'html')
                                                    {{-- Raw by request: `type => 'html'` is the opt-in for
                                                         unescaped output. --}}
                                                    {!! $showField['html'] ?? $$routeModel[$columnName] !!}
                                                @elseif ($type == 'decimal')
                                                    {{ number_format($$routeModel[$columnName], $showField['decimal']) }}
                                                @elseif ($type == 'url')
                                                    @php
                                                        // $url was never assigned here, so the link always
                                                        // rendered with an empty href.
                                                        $url = $$routeModel[$columnName] ?? null;
                                                        $target = $showField['target'] ?? '_blank';

                                                        // See table.blade.php: an escaped attribute still lets a
                                                        // `javascript:` href execute on click.
                                                        if (is_string($url) && $url !== '') {
                                                            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
                                                            if ($scheme !== '' && !in_array($scheme, ['http', 'https', 'mailto', 'tel'], true)) {
                                                                $url = null;
                                                            }
                                                        } else {
                                                            $url = null;
                                                        }
                                                    @endphp
                                                    @if (!empty($url))
                                                        <a href="{{ $url }}" target="{{ $target }}" @if ($target === '_blank') rel="noopener noreferrer" @endif>
                                                            {{ $showField['displayText'] ?? 'View' }}
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                @elseif ($type == 'decimal_with_currency')
                                                    {{ $$routeModel[$columnName] . ' ' . $showField['currency'] }}
                                                @elseif ($type == 'dollar_decimal')
                                                    $ {{ number_format($$routeModel[$columnName], $showField['decimal']) }}
                                                @endif
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        @can('update', $$routeModel)
                                            <a href="{{ route($viewPrefix . '.edit', $$routeModel->getRouteKey()) }}" class="btn btn-success me-3">Edit</a>
                                        @endcan
                                        <a href="{{ route($viewPrefix . '.index') }}" class="btn btn-dark me-3">Back</a>
                                    </div>
                                    @can('delete', $$routeModel)
                                        <form action="{{ route($viewPrefix . '.destroy', $$routeModel->getRouteKey()) }}" method="POST" enctype="multipart/form-data">
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
                        @if (View::exists($viewPrefix . '.show-right-box'))
                            @include($viewPrefix . '.show-right-box')
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
