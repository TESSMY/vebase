<div class="row mb-2">
    @if (request()->routeIs($routePrefix . '.' . $routeName . '.create'))
        @foreach ($model->createFields as $createField)
            <div class="col-12 {{ $createField['size'] ?? 'col-md-6' }} mb-md-2 mb-2">
                @if (!empty($createField['displayName']))
                    <label class="form-label">{{ $createField['displayName'] }}</label>
                @endif
                @if ($createField['inputType'] == 'select')
                    <select class="form-select" name="{{ $createField['name'] }}" {{ !empty($createField['required']) ? 'required' : '' }}>
                        @foreach ($createField['options'] as $key => $option)
                            <option value="{{ $key }}">{{ $option }}</option>
                        @endforeach
                    </select>
                @elseif ($createField['inputType'] == 'countryselect') 
                    <country-select :countries="{{ json_encode(array_values(countries())) }}" name="{{ $createField['name'] }}" data-name="{{ $createField['dataName'] }}"></country-select>
                @elseif ($createField['inputType'] == 'textarea') 
                    <textarea class="form-control" name="{{ $createField['name'] }}" placeholder="{{ $createField['placeholder'] }}" rows="{{ $createField['rows'] ?? 5 }}" {{ !empty($createFields['required']) && $createFields['required'] == 'true' ? 'required' : '' }}>{{ old($createField['name']) ?? '' }}</textarea>
                @elseif ($createField['inputType'] == 'radio' || $createField['inputType'] == 'checkbox')
                    @if (!empty($createField['multipleInput']))
                        @foreach ($createField['options'] as $option)
                            <div class="form-check mb-2 {{ !empty($createField['switchType']) && $createField['switchType'] == 'true' ? 'form-switch' : '' }}">
                                <input class="form-check-input" type="{{ $option['inputType'] }}" name="{{ $option['name'] }}" id="{{ $option['id'] }}" value="{{ $option['value'] }}" {{ !empty($createFields['required']) && $createFields['required'] == 'true' ? 'required' : '' }}>
                                <label class="form-check-label" for="{{ $option['id'] }}">{{ $option['displayValue'] }}</label>
                            </div>
                        @endforeach
                    @else
                        <div class="form-check">
                            <input class="form-check-input" type="{{ $createField['inputType'] }}" name="{{ $createField['name'] }}" id="{{ $createField['id'] }}" value="{{ $createField['value'] }}" {{ !empty($createFields['required']) && $createFields['required'] == 'true' ? 'required' : '' }}>
                            <label class="form-check-label" for="{{ $createField['id'] }}">{{ $createField['displayValue'] }}</label>
                        </div>
                    @endif
                @elseif ($createField['inputType'] == 'range')
                    <input class="form-range" type="{{ $createField['inputType'] }}" min={{ $createField['min'] ?? '' }} max={{ $createField['max'] ?? '' }} step={{ $createField['step'] ?? '' }} name="{{ $createField['name'] }}" placeholder="{{ $createField['placeholder'] }}" value="{{ old($createField['name']) ?? '' }}" {{ !empty($createFields['required']) && $createFields['required'] == 'true' ? 'required' : '' }}>
                @elseif ($createField['inputType'] == 'number')
                    <input class="form-control" type="{{ $createField['inputType'] }}" min={{ $createField['min'] ?? '' }} max={{ $createField['max'] ?? '' }} step={{ $createField['step'] ?? '' }} name="{{ $createField['name'] }}" placeholder="{{ $createField['placeholder'] }}" value="{{ old($createField['name']) ?? '' }}" {{ !empty($createFields['required']) && $createFields['required'] == 'true' ? 'required' : '' }}>
                @else 
                    <input class="form-control" type="{{ $createField['inputType'] }}" name="{{ $createField['name'] }}" placeholder="{{ $createField['placeholder'] }}" value="{{ old($createField['name']) ?? '' }}" {{ !empty($createField['required']) && $createField['required'] == 'true' ? 'required' : '' }}>
                @endif
            </div>
        @endforeach
    @else
        @foreach ($model->updateFields as $updateField)
            <div class="col-12 {{ $updateField['size'] ?? 'col-md-6' }} mb-md-2 mb-2">
                @if (!empty($updateField['displayName']))
                    <label class="form-label">{{ $updateField['displayName'] }}</label>
                @endif
                @if ($updateField['inputType'] == 'select')
                    <select class="form-select" name="{{ $updateField['name'] }}" {{ !empty($updateField['required']) ? 'required' : '' }}>
                        @foreach ($updateField['options'] as $key => $option)
                            <option value="{{ $key }}" {{ !empty($$routeModel) && $$routeModel[$updateField['name']] == $key ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                @elseif ($updateField['inputType'] == 'countryselect') 
                    <country-select :countries="{{ json_encode(array_values(countries())) }}" :current-country="{{ json_encode($$routeModel[$updateField['name']]) }}" name="{{ $updateField['name'] }}" data-name="{{ $updateField['dataName'] }}"></country-select>
                @elseif ($updateField['inputType'] == 'textarea') 
                    <textarea class="form-control" name="{{ $updateField['name'] }}" placeholder="{{ $updateField['placeholder'] }}" rows="{{ $updateField['rows'] ?? 5 }}" {{ !empty($updateField['required']) && $updateField['required'] == 'true' ? 'required' : '' }}>{{ old($updateField['name']) ?? (!empty($$routeModel) ? $$routeModel[$updateField['name']] : '') }}</textarea>
                @elseif ($updateField['inputType'] == 'radio' || $updateField['inputType'] == 'checkbox')
                    @if (!empty($updateField['multipleInput']))
                        @foreach ($updateField['options'] as $key => $option)
                            <div class="form-check mb-2 {{ !empty($updateField['switchType']) && $updateField['switchType'] == 'true' ? 'form-switch' : '' }}">
                                <input class="form-check-input" type="{{ $option['inputType'] }}" name="{{ $option['name'] }}" id="{{ $option['id'] }}" value="{{ $option['value'] }}" {{ !empty($$routeModel) && $$routeModel[$updateField['name']] == $key ? 'checked' : '' }} {{ !empty($updateField['required']) && $updateField['required'] == 'true' ? 'required' : '' }} {{ boolval($$routeModel[$updateField['name']]) == 'true' ? 'checked' : ''}}>
                                <label class="form-check-label" for="{{ $option['id'] }}">{{ $option['displayValue'] }}</label>
                            </div>
                        @endforeach
                    @else
                    <div class="form-check">
                        <input class="form-check-input" type="{{ $updateField['inputType'] }}" name="{{ $updateField['name'] }}" id="{{ $updateField['id'] }}" value="{{ $updateField['value'] }}" {{ !empty($updateField['required']) && $updateField['required'] == 'true' ? 'required' : '' }} {{ boolval($$routeModel[$updateField['name']]) == 'true' ? 'checked' : ''}}>
                        <label class="form-check-label" for="{{ $updateField['id'] }}">{{ $updateField['displayValue'] }}</label>
                    </div>
                    @endif
                @elseif ($updateField['inputType'] == 'range')
                    <input class="form-range" type="{{ $updateField['inputType'] }}" min={{ $updateField['min'] ?? '' }} max={{ $updateField['max'] ?? '' }} step={{ $updateField['step'] ?? '' }} name="{{ $updateField['name'] }}" placeholder="{{ $updateField['placeholder'] }}" value="{{ old($updateField['name']) ?? (!empty($$routeModel) ? $$routeModel[$updateField['name']] : '') }}" {{ !empty($updateField['required']) && $updateField['required'] == 'true' ? 'required' : '' }}>
                @elseif ($updateField['inputType'] == 'number')
                    <input class="form-control" type="{{ $updateField['inputType'] }}" min={{ $updateField['min'] ?? '' }} max={{ $updateField['max'] ?? '' }} step={{ $updateField['step'] ?? '' }} name="{{ $updateField['name'] }}" placeholder="{{ $updateField['placeholder'] }}" value="{{ old($updateField['name']) ?? (!empty($$routeModel) ? $$routeModel[$updateField['name']] : '') }}" {{ !empty($updateField['required']) && $updateField['required'] == 'true' ? 'required' : '' }}>
                @elseif ($updateField['inputType'] == 'file')
                    <input class="form-control" type="{{ $updateField['inputType'] }}" name="{{ $updateField['name'] }}" placeholder="{{ $updateField['placeholder'] }}" {{ !empty($updateField['required']) && $updateField['required'] == 'true' ? 'required' : '' }}>
                @else
                    <input class="form-control" type="{{ $updateField['inputType'] }}" name="{{ $updateField['name'] }}" placeholder="{{ $updateField['placeholder'] }}" value="{{ old($updateField['name']) ?? (!empty($$routeModel) ? $$routeModel[$updateField['name']] : '') }}" {{ !empty($updateField['required']) && $updateField['required'] == 'true' ? 'required' : '' }}>
                @endif
            </div>
        @endforeach
    @endif
</div>