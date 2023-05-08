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
                                @sortablelink($indexField['columnName'], $indexField['displayName'])
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
                            @if (strtolower($indexField['columnName']) == 'show') 
                                @can('view', $$routeModel)
                                    <td><a href="{{ route($routePrefix . '.' . $routeName . '.show', $$routeModel->getRouteKey()) }}"><i class="uil-eye"></i></a></td>
                                @endcan
                            @elseif (strtolower($indexField['columnName']) == 'edit') 
                                @can('update', $$routeModel)
                                    <td><a href="{{ route($routePrefix . '.' . $routeName . '.edit', $$routeModel->getRouteKey()) }}"><i class="uil-edit"></i></a></td>
                                @endcan
                            @elseif (strtolower($indexField['columnName']) == 'show_and_edit') 
                                <td>
                                    @can('view', $$routeModel)
                                        <a class="me-3" href="{{ route($routePrefix . '.' . $routeName . '.show', $$routeModel->getRouteKey()) }}"><i class="uil-eye"></i></a>
                                    @endcan
                                    @can('update', $$routeModel)
                                        <a href="{{ route($routePrefix . '.' . $routeName . '.edit', $$routeModel->getRouteKey()) }}"><i class="uil-edit"></i></a>
                                    @endcan
                                </td>
                            @else
                                @if (is_null($$routeModel[$indexField['columnName']]))
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
                @endif
            @empty
                <tr>
                    <td colspan="100%" class="text-center">There are no {{ $modelName }} found.</td>
                </tr>
            @endforelse
        </tbody>
    @endif
</table>