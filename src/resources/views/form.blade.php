<div class="row">
    @if (request()->routeIs($routePrefix . '.' . $routeName . '.create'))
        @foreach ($model->createFields as $createField)
            <div class="col-12 {{ $createField['size'] ?? 'col-md-6' }} mb-md-2 mb-2">
                <label>{{ $createField['displayName'] }}</label>
                @if ($createField['inputType'] == 'select')
                    <select class="form-select" name="{{ $createField['name'] }}">
                        @foreach ($createField['options'] as $key => $option)
                            <option value="{{ $key }}">{{ $option }}</option>
                        @endforeach
                    </select>
                @elseif ($createField['inputType'] == 'textarea') 
                    <textarea class="form-control" name="{{ $createField['name'] }}" placeholder="{{ $createField['placeholder'] }}" rows="{{ $createField['rows'] ?? 5 }}" {{ $createField['type'] }}></textarea>
                @elseif ($createField['inputType'] == 'radio' || $createField['inputType'] == 'checkbox')
                    @if (!empty($createField['multipleInput']))
                        @foreach ($createField['options'] as $option)
                            <div class="form-check mb-2 {{ !empty($createField['switchType']) && $createField['switchType'] == 'true' ? 'form-switch' : '' }}">
                                <input class="form-check-input" type="{{ $option['inputType'] }}" name="{{ $option['name'] }}" id="{{ $option['id'] }}" value="{{ $option['value'] }}" {{ $option['type'] }}>
                                <label class="form-check-label" for="{{ $option['id'] }}">{{ $option['displayValue'] }}</label>
                            </div>
                        @endforeach
                    @else
                        <div>
                            <input class="form-check-input" type="{{ $createField['inputType'] }}" name="{{ $createField['name'] }}" id="{{ $createField['id'] }}" value="{{ $createField['value'] }}" {{ $createField['type'] }}>
                            <label class="form-check-label" for="{{ $createField['id'] }}">{{ $createField['displayValue'] }}</label>
                        </div>
                    @endif
                @elseif ($createField['inputType'] == 'range')
                    <input class="form-range" type="{{ $createField['inputType'] }}" min={{ $createField['min'] ?? '' }} max={{ $createField['max'] ?? '' }} step={{ $createField['step'] ?? '' }} name="{{ $createField['name'] }}" placeholder="{{ $createField['placeholder'] }}" value="{{ old($createField['name']) ?? (!empty($$routeModel) ? $$routeModel[$createField['name']] : '') }}" {{ $createField['type'] }}>
                @elseif ($createField['inputType'] == 'number')
                    <input class="form-control" type="{{ $createField['inputType'] }}" min={{ $createField['min'] ?? '' }} max={{ $createField['max'] ?? '' }} step={{ $createField['step'] ?? '' }} name="{{ $createField['name'] }}" placeholder="{{ $createField['placeholder'] }}" value="{{ old($createField['name']) ?? (!empty($$routeModel) ? $$routeModel[$createField['name']] : '') }}" {{ $createField['type'] }}>
                @else 
                    <input class="form-control" type="{{ $createField['inputType'] }}" name="{{ $createField['name'] }}" placeholder="{{ $createField['placeholder'] }}" value="{{ old($createField['name']) ?? (!empty($$routeModel) ? $$routeModel[$createField['name']] : '') }}" {{ $createField['type'] }}>
                @endif
            </div>
        @endforeach
    @else
        @foreach ($model->updateFields as $updateField)
            <div class="col-12 col-md-6 mb-md-2 mb-2">
                <label>{{ $updateField['displayName'] }}</label>
                <input class="form-control" type="{{ $updateField['inputType'] }}" name="{{ $updateField['name'] }}" placeholder="{{ $updateField['placeholder'] }}" value="{{ old($updateField['name']) ?? (!empty($$routeModel) ? $$routeModel[$updateField['name']] : '') }}" {{ $updateField['type'] }}>
            </div>
        @endforeach
    @endif
</div>
<div class="row col-12">
    <button type="submit" class="col-12 col-md-1 btn btn-success m-2">{{ request()->routeIs($routePrefix . '.' . $routeName . '.create') ? 'Create' : 'Update' }}</button>
    <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
</div>