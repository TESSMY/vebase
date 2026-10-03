@php
    /*
     * Everything that does not vary per row is resolved once, here.
     *
     * The per-cell View::exists() calls were the expensive part: the view finder caches hits
     * but not misses, so every miss re-walked the view paths on disk. At rows x columns that
     * was thousands of filesystem lookups for a single listing, plus a permission and role
     * check per cell.
     */
    $authUser = auth()->user();
    $viewPrefix = $routePrefix . '.' . $routeName;

    $superAdminRole = defined('\App\Models\User::ROLE_SUPER_ADMIN') ? [\App\Models\User::ROLE_SUPER_ADMIN] : [];

    $veFields = [];
    foreach ($model->indexFields as $indexField) {
        $columnName = $indexField['columnName'] ?? strtolower(Str::snake($indexField['displayName']));

        $showField = true;
        foreach ($indexField['permissions'] ?? [] as $permission) {
            if (empty($authUser) || !$authUser->can($permission)) {
                $showField = false;
                break;
            }
        }
        if ($showField && !empty($indexField['roles'])) {
            // Previously a `break` here, inside an `if` nested in this loop -- it broke out of
            // the field loop itself, so one role-gated column silently dropped every column
            // after it rather than just itself.
            $showField = !empty($authUser) && $authUser->hasAnyRole(array_merge($indexField['roles'], $superAdminRole));
        }
        if (!$showField) {
            continue;
        }

        $veFields[] = [
            'field' => $indexField,
            'columnName' => $columnName,
            'thView' => View::exists($viewPrefix . '.index.' . $columnName . '-th') ? $viewPrefix . '.index.' . $columnName . '-th' : null,
            'cellView' => View::exists($viewPrefix . '.index.' . $columnName) ? $viewPrefix . '.index.' . $columnName : null,
        ];
    }

    $sortableColumns = $model->sortable ?? [];
    $headView = View::exists($viewPrefix . '.index-table-head') ? $viewPrefix . '.index-table-head' : null;
    $thView = View::exists($viewPrefix . '.index-table-th') ? $viewPrefix . '.index-table-th' : null;
    $bodyView = View::exists($viewPrefix . '.index-table-body') ? $viewPrefix . '.index-table-body' : null;
    $rowView = View::exists($viewPrefix . '.index-table-tr') ? $viewPrefix . '.index-table-tr' : null;
@endphp
<table class="table">
    @if ($headView)
        @include($headView)
    @else
        <thead>
        <tr>
            @if ($thView)
                @include($thView)
            @else
                @foreach ($veFields as $veField)
                    @php
                        $indexField = $veField['field'];
                        $columnName = $veField['columnName'];
                    @endphp
                    <th>
                        @if ($veField['thView'])
                            @include($veField['thView'], [$routeModel => $model])
                        @elseif (strtolower($indexField['displayName']) == strtolower('Actions') || strtolower($indexField['displayName']) == strtolower('Action'))
                            {{ $indexField['displayName'] }}
                        @else
                            @if (!empty($sortableColumns) && !empty($indexField['columnName']) && in_array($indexField['columnName'], $sortableColumns))
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

    @if ($bodyView)
        @include($bodyView)
    @else
        <tbody>
        @forelse ($models as $$routeModel)
            @if ($rowView)
                @include($rowView)
            @else
                <tr>
                    @foreach ($veFields as $veField)
                        @php
                            $indexField = $veField['field'];
                            $columnName = $veField['columnName'];
                        @endphp
                        @if ($veField['cellView'])
                            @include($veField['cellView'], ['data' => $$routeModel])
                        @elseif ($columnName == 'show')
                            @can('view', $$routeModel)
                                <td><a href="{{ route($viewPrefix . '.show', $$routeModel->getRouteKey()) }}"><i class="uil-eye"></i></a></td>
                            @endcan
                        @elseif ($columnName == 'edit')
                            @can('update', $$routeModel)
                                <td><a href="{{ route($viewPrefix . '.edit', $$routeModel->getRouteKey()) }}"><i class="uil-edit"></i></a></td>
                            @endcan
                        @elseif ($columnName == 'show_and_edit')
                            <td>
                                @can('view', $$routeModel)
                                    <a class="me-3" href="{{ route($viewPrefix . '.show', $$routeModel->getRouteKey()) }}"><i class="uil-eye"></i></a>
                                @endcan
                                @can('update', $$routeModel)
                                    <a href="{{ route($viewPrefix . '.edit', $$routeModel->getRouteKey()) }}"><i class="uil-edit"></i></a>
                                @endcan
                            </td>
                        @else
                            {{-- v-pre on every data cell: the page is compiled by Vue's in-DOM
                                 template compiler, so a stored value such as a name of
                                 `{{ ... }}` would otherwise run as a Vue expression for
                                 whoever opens the listing. Blade's escaping does not stop it. --}}
                            @if (empty($indexField['type']))
                                @if (!isset($$routeModel[$columnName]))
                                    <td v-pre>-</td>
                                @else
                                    <td v-pre>{{ $$routeModel[$columnName] }}</td>
                                @endif
                            @elseif ($indexField['type'] == 'image')
                                <td v-pre>
                                    @if (!empty($$routeModel[$columnName]))
                                        <img src="{{ $$routeModel[$columnName] }}" class="avatar">
                                    @endif
                                </td>
                            @elseif ($indexField['type'] == 'span')
                                <td v-pre>
                                    <span class="{{ $$routeModel[$indexField['class']] ?? '' }}">{{ $$routeModel[$columnName] }}</span>
                                </td>
                            @elseif ($indexField['type'] == 'boolean')
                                <td v-pre>{{ !empty($$routeModel[$columnName]) ? 'Yes' : 'No' }}</td>
                            @elseif ($indexField['type'] == 'relation')
                                @php
                                    $relation = explode('.', $indexField['relation']);
                                    $data = $$routeModel;
                                    for ($i = 0; $i < count($relation); $i++) {
                                        $data = $data?->{$relation[$i]};
                                    }
                                @endphp
                                <td v-pre>{{ $data?->{$indexField['relatedColumnName']} ?? '-' }}</td>
                            @elseif ($indexField['type'] == 'html')
                                {{-- Raw by request: `type => 'html'` is the opt-in for unescaped output.
                                     Only point it at a column whose contents you control. --}}
                                <td v-pre>{!! $indexField['html'] ?? $$routeModel[$columnName] !!}</td>
                            @elseif ($indexField['type'] == 'decimal')
                                <td v-pre>{{ number_format($$routeModel[$columnName], $indexField['decimal']) }}</td>
                            @elseif ($indexField['type'] == 'url')
                                @php
                                    $url = $$routeModel[$columnName] ?? null;
                                    $target = $indexField['target'] ?? '_blank';

                                    // Blade escapes the attribute value, but that does not stop a
                                    // `javascript:` or `data:` href from running when the link is
                                    // clicked -- so a stored URL becomes stored XSS. Only schemes
                                    // that navigate are allowed through; relative URLs have none.
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
                                    <td v-pre>
                                        <a href="{{ $url }}" target="{{ $target }}" @if ($target === '_blank') rel="noopener noreferrer" @endif>
                                            {{ $indexField['displayText'] ?? 'View' }}
                                        </a>
                                    </td>
                                @else
                                    <td v-pre>-</td>
                                @endif
                            @elseif ($indexField['type'] == 'decimal_with_currency')
                                <td v-pre>{{ $$routeModel[$columnName] . ' ' . $indexField['currency'] }}</td>
                            @elseif ($indexField['type'] == 'dollar_decimal')
                                <td v-pre>$ {{ number_format($$routeModel[$columnName], $indexField['decimal']) }}</td>
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
