<div class="row">
    @foreach ($model::createFields as $createField)
        <div class="col-12 col-md-6 mb-md-2 mb-2">
            <label>{{ $createField['displayName'] }}</label>
            <input class="form-control" type="{{ $createField['type'] }}" name="{{ $createField['name'] }}" placeholder="{{ $createField['displayName'] }}" value="{{ old($createField['name']) ?? (!empty($model) ? $model[$createField['name']] : '') }}" {{ $createField['required'] }}>
        </div>
    @endforeach
</div>
<div class="row col-12">
    <button type="submit" class="col-12 col-md-1 btn btn-success m-2">{{ request()->routeIs($routePrefix . '.' . $routeName . '.create') ? 'Create' : 'Update' }}</button>
    <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
</div>