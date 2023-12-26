<table class="table">
    @if (View::exists($routePrefix . '.' . $routeName . '.index-table-head'))
        @include($routePrefix . '.' . $routeName . '.index-table-head')
    @else
        <thead>
            <tr>
                @foreach ($model->indexFields as $indexField)
                    @if (View::exists($routePrefix . '.' . $routeName . '.index-table-th'))
                        @include($routePrefix . '.' . $routeName . '.index-table-th')  
                    @else
                        <th>
                            @if (strtolower($indexField['displayName']) == strtolower('Actions') || strtolower($indexField['displayName']) == strtolower('Action'))
                                {{ $indexField['displayName'] }}
                            @else
                                @if (!empty($model->sortable) && in_array($indexField['columnName'], $model->sortable))
                                    @sortablelink($indexField['columnName'], $indexField['displayName'])    
                                @else
                                    {{ $indexField['displayName'] }}
                                @endif
                            @endif
                        </th>
                    @endif
                @endforeach
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
                            @if (!empty($indexField['columnName']) && strtolower($indexField['columnName']) == 'show') 
                                @can('view', $$routeModel)
                                    <td><a href="{{ route($routePrefix . '.' . $routeName . '.show', $$routeModel->getRouteKey()) }}"><i class="uil-eye"></i></a></td>
                                @endcan
                            @elseif (!empty($indexField['columnName']) && strtolower($indexField['columnName']) == 'edit') 
                                @can('update', $$routeModel)
                                    <td><a href="{{ route($routePrefix . '.' . $routeName . '.edit', $$routeModel->getRouteKey()) }}"><i class="uil-edit"></i></a></td>
                                @endcan
                            @elseif (!empty($indexField['columnName']) && strtolower($indexField['columnName']) == 'show_and_edit') 
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
                                    @if (!isset($$routeModel[$indexField['columnName']]))
                                        <td>-</td>
                                    @else
                                        <td>{{ $$routeModel[$indexField['columnName']] }}</td>
                                    @endif
                                @elseif ($indexField['type'] == 'image')
                                    <td>
                                        @if (!empty($$routeModel[$indexField['columnName']]))
                                            <img src="{{ $$routeModel[$indexField['columnName']] }}" class="avatar">
                                        @endif
                                    </td>
                                @elseif ($indexField['type'] == 'span')
                                    <td>
                                        <span class="{{ $$routeModel[$indexField['class']] ?? '' }}">{{ $$routeModel[$indexField['columnName']] }}</span>
                                    </td>
                                @elseif ($indexField['type'] == 'relation')
                                    @if (empty($$routeModel[$indexField['relation']][$indexField['relatedColumnName']]))
                                        <td>-</td>
                                    @else
                                        <td>{{ $$routeModel[$indexField['relation']][$indexField['relatedColumnName']] }}</td>
                                    @endif
                                @elseif ($indexField['type'] == 'html')
                                    <td>{!! $indexField['html'] !!}</td>
                                @elseif ($indexField['type'] == 'decimal')
                                    <td>{{ number_format($$routeModel[$indexField['columnName']], $indexField['decimal']) }}</td>
                                @elseif ($indexField['type'] == 'decimal_with_currency')
                                    <td>{{ $$routeModel[$indexField['columnName']] . ' ' . $indexField['currency'] }}</td>
                                @elseif ($indexField['type'] == 'dollar_decimal')
                                    <td>$ {{ number_format($$routeModel[$indexField['columnName']], $indexField['decimal']) }}</td>
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