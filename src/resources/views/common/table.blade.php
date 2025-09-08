<table class="table">
    @php
        $authUser = auth()->user();
    @endphp
    @if (View::exists($routePrefix . '.' . $routeName . '.index-table-head'))
        @include($routePrefix . '.' . $routeName . '.index-table-head')
    @else
        <thead>
        <tr>
            @if (View::exists($routePrefix . '.' . $routeName . '.index-table-th'))
                @include($routePrefix . '.' . $routeName . '.index-table-th')
            @else
                @foreach ($model->indexFields as $indexField)
                    @php

                        $columnName = $indexField['columnName'] ?? strtolower(Str::snake($indexField['displayName']));
                        $showField = true;
                        if (!empty($indexField['permissions'])) {
                            foreach ($indexField['permissions'] as $permission) {
                                if (empty($authUser) || !$authUser->can($permission)) {
                                    $showField = false;
                                    break;
                                }
                            }
                        }
                        if (!empty($indexField['roles'])) {
                            if (empty($authUser) || !$authUser->hasAnyRole(array_merge($indexField['roles'], [\App\Models\User::ROLE_SUPER_ADMIN]))) {
                                $showField = false;
                                break;
                            }
                        }
                        if (!$showField) {
                            continue;
                        }
                    @endphp
                    <th>
                        @if (View::exists($routePrefix . '.' . $routeName . '.index.' . $columnName . '-th'))
                            @include($routePrefix . '.' . $routeName . '.index.' . $columnName . '-th', ['data' => $$routeModel])
                        @elseif (strtolower($indexField['displayName']) == strtolower('Actions') || strtolower($indexField['displayName']) == strtolower('Action'))
                            {{ $indexField['displayName'] }}
                        @else
                            @if (!empty($model->sortable) && !empty($indexField['columnName']) && in_array($indexField['columnName'], $model->sortable))
                                @sortablelink($indexField['columnName'], $indexField['displayName'])
                            @else
                                {{ $indexField['displayName'] }}
                            @endif
                        @endif
                    </th>
                @endforeach
            @endif
        </tr>
        </thead>
    @endif

    @if (View::exists($routePrefix . '.' . $routeName . '.index-table-body'))
        @include($routePrefix . '.' . $routeName . '.index-table-body')
    @else
        <tbody>
        @forelse ($models as $$routeModel)
            @if (View::exists($routePrefix . '.' . $routeName . '.index-table-tr'))
                @include($routePrefix . '.' . $routeName . '.index-table-tr')
            @else
                <tr>
                    @foreach ($model->indexFields as $indexField)
                        @php
                            $columnName = $indexField['columnName'] ?? strtolower(Str::snake($indexField['displayName']));
                            $showField = true;

                            if (!empty($indexField['permissions'])) {
                                foreach ($indexField['permissions'] as $permission) {
                                    if (empty($authUser) || !$authUser->can($permission)) {
                                        $showField = false;
                                        break;
                                    }
                                }
                            }
                            if (!empty($indexField['roles'])) {
                                if (empty($authUser) || !$authUser->hasAnyRole(array_merge($indexField['roles'], [\App\Models\User::ROLE_SUPER_ADMIN]))) {
                                    $showField = false;
                                    break;
                                }
                            }
                            if (!$showField) {
                                continue;
                            }
                        @endphp
                        @if (View::exists($routePrefix . '.' . $routeName . '.index.' . $columnName))
                            @include($routePrefix . '.' . $routeName . '.index.' . $columnName, ['data' => $$routeModel])
                        @elseif ($columnName == 'show')
                            @can('view', $$routeModel)
                                <td><a href="{{ route($routePrefix . '.' . $routeName . '.show', $$routeModel->getRouteKey()) }}"><i class="uil-eye"></i></a></td>
                            @endcan
                        @elseif ($columnName == 'edit')
                            @can('update', $$routeModel)
                                <td><a href="{{ route($routePrefix . '.' . $routeName . '.edit', $$routeModel->getRouteKey()) }}"><i class="uil-edit"></i></a></td>
                            @endcan
                        @elseif ($columnName == 'show_and_edit')
                            <td>
                                @can('view', $$routeModel)
                                    <a class="me-3" href="{{ route($routePrefix . '.' . $routeName . '.show', $$routeModel->getRouteKey()) }}"><i class="uil-eye"></i></a>
                                @endcan
                                @can('update', $$routeModel)
                                    <a href="{{ route($routePrefix . '.' . $routeName . '.edit', $$routeModel->getRouteKey()) }}"><i class="uil-edit"></i></a>
                                @endcan
                            </td>
                        @else
                            @if (empty($indexField['type']))
                                @if (!isset($$routeModel[$columnName]))
                                    <td>-</td>
                                @else
                                    <td>{{ $$routeModel[$columnName] }}</td>
                                @endif
                            @elseif ($indexField['type'] == 'image')
                                <td>
                                    @if (!empty($$routeModel[$columnName]))
                                        <img src="{{ $$routeModel[$columnName] }}" class="avatar">
                                    @endif
                                </td>
                            @elseif ($indexField['type'] == 'span')
                                <td>
                                    <span class="{{ $$routeModel[$indexField['class']] ?? '' }}">{{ $$routeModel[$columnName] }}</span>
                                </td>
                            @elseif ($indexField['type'] == 'relation')
                                @php
                                    $relation = explode('.', $indexField['relation']);
                                    $data = $$routeModel;
                                    for ($i = 0; $i < count($relation); $i++) {
                                        $data = $data?->{$relation[$i]};
                                    }
                                @endphp
                                <td>{{ $data?->{$indexField['relatedColumnName']} ?? '-' }}</td>
                            @elseif ($indexField['type'] == 'html')
                                <td>{!! $indexField['html'] !!}</td>
                            @elseif ($indexField['type'] == 'decimal')
                                <td>{{ number_format($$routeModel[$columnName], $indexField['decimal']) }}</td>
                            @elseif ($indexField['type'] == 'url')
                                @php
                                    $url = $$routeModel[$columnName] ?? null;
                                    $target = $indexField['target'] ?? '_blank';
                                @endphp
                                @if (!empty($url))
                                    <td>
                                        <a href="{{ $url }}" target="{{ $target }}">
                                            {{ $indexField['displayText'] ?? 'View' }}
                                        </a>
                                    </td>
                                @else
                                    <td>-</td>
                                @endif
                            @elseif ($indexField['type'] == 'decimal_with_currency')
                                <td>{{ $$routeModel[$columnName] . ' ' . $indexField['currency'] }}</td>
                            @elseif ($indexField['type'] == 'dollar_decimal')
                                <td>$ {{ number_format($$routeModel[$columnName], $indexField['decimal']) }}</td>
                            @endif
                        @endif
                    @endforeach
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="100%" class="text-center">There are no {{ \Illuminate\Support\Str::plural(strtolower($modelName)) }} found.</td>
            </tr>
        @endforelse
        </tbody>
    @endif
</table>
