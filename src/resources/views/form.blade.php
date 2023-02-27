<div class="row">
    @if (request()->routeIs($routePrefix . '.' . $routeName . '.create'))
        @foreach ($model::createFields as $createField)
            <div class="col-12 col-md-6 mb-md-2 mb-2">
                <label>{{ $createField['displayName'] }}</label>
            </div>
        @endforeach
    @else
        @foreach ($model::updateFields as $updateField)
            <div class="col-12 col-md-6 mb-md-2 mb-2">
                <label>{{ $updateField['displayName'] }}</label>
                <input class="form-control" type="{{ $updateField['type'] }}" name="{{ $updateField['name'] }}" placeholder="{{ $updateField['displayName'] }}" value="{{ old($updateField['name']) ?? (!empty($$routeModel) ? $$routeModel[$updateField['name']] : '') }}" {{ $updateField['required'] }}>
            </div>
        @endforeach
    @endif
</div>
<div class="row col-12">
    <button type="submit" class="col-12 col-md-1 btn btn-success m-2">{{ request()->routeIs($routePrefix . '.' . $routeName . '.create') ? 'Create' : 'Update' }}</button>
    <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
</div>