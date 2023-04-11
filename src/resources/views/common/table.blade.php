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