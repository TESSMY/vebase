@php
    $isCreate = request()->routeIs('*.create');
    $fields = $isCreate ? $model->createFields : $model->updateFields;
    $authUser = auth()->user();
@endphp
<div class="row mb-2">
    @foreach ($fields as $field)
        @php
            $field['displayName'] = $field['displayName'] ?? ucwords(str_replace('_', ' ', $field['name']));
            $field['inputType'] = $field['inputType'] ?? 'text';
            $field['placeholder'] = $field['placeholder'] ?? $field['displayName'];
            $value = old($field['name']) ?? (!$isCreate && !empty($$routeModel) ? $$routeModel[$field['name']] ?? '' : ($field['default'] ?? ''));
            $showField = true;

            // accept array value
            if ($field['inputType'] == 'textarea' && !$isCreate) {
                $value = old($field['name']) ?? (!empty($$routeModel)
                    ? (is_array($$routeModel[$field['name']])
                        ? json_encode($$routeModel[$field['name']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : $$routeModel[$field['name']])
                    : '');
            }

            if (!empty($field['permissions'])) {
                foreach ($field['permissions'] as $permission) {
                    if (empty($authUser) || !$authUser->can($permission)) {
                        $showField = false;
                        break;
                    }
                }
            }
            if (!empty($field['roles'])) {
                if (empty($authUser) || !$authUser->hasAnyRole(array_merge($field['roles'], [\App\Models\User::ROLE_SUPER_ADMIN]))) {
                    $showField = false;
                }
            }
            if (!$showField) {
                continue;
            }

            if (!empty($field['class'])) {
                $data = $field['class']::query();
                if (!empty($field['where'])) {
                    foreach ($field['where'] as $condition) {
                        $data->where($condition[0], $condition[1], $condition[2]);
                    }
                }
                $data = $data->get();
                $field['options'] = [];
                foreach ($data as $item) {
                    $field['options'][$item[$field['key'] ?? 'id']] = $item[$field['value'] ?? 'name'];
                }
            }
        @endphp
        @if ($showField)
            <div class="col-12 {{ $field['size'] ?? 'col-md-6' }} mb-md-2 mb-2">
                @if (!empty($field['displayName']))
                    <label class="form-label">{{ $field['displayName'] }}</label>
                @endif
                @if ($field['inputType'] == 'select')
                    <select class="form-select" name="{{ $field['name'] }}" {{ !empty($field['required']) ? 'required' : '' }}>
                        @if (!empty($field['includeEmpty']))
                            <option value="">N/A</option>
                        @endif
                        @foreach ($field['options'] as $key => $option)
                            <option value="{{ $key }}" {{ $value == $key ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                @elseif ($field['inputType'] == 'countryselect')
                    <country-select
                            :countries="{{ json_encode(array_values(countries())) }}"
                            name="{{ $field['name'] }}"
                            data-name="{{ $field['dataName'] }}"
                            @if (!$isCreate)
                                :current-country="{{ json_encode($value) }}"
                            @endif
                    ></country-select>
                @elseif ($field['inputType'] == 'tagging')
                    <tagging
                            name="{{ $field['name'] }}"
                            placeholder="{{ $field['placeholder'] }}"
                            :options='@json($field["options"])'
                            label="{{ $field['label'] }}"
                            track-by="{{ $field['trackBy'] }}"
                            :required='@json(!empty($field["required"]))'
                            :allow-add-new-tag='@json(!empty($field["allowAddNewTag"]))'
                            @if (!$isCreate)
                                :current-values='@json(is_array($value) ? $value : [$value])'
                            @endif
                    ></tagging>
                @elseif ($field['inputType'] == 'textarea')
                    <textarea class="form-control" name="{{ $field['name'] }}" placeholder="{{ $field['placeholder'] }}" rows="{{ $field['rows'] ?? 5 }}" {{ !empty($field['required']) ? 'required' : '' }}>{{ $value }}</textarea>
                @elseif ($field['inputType'] == 'radio' || $field['inputType'] == 'checkbox')
                    @if (!empty($field['multipleInput']))
                        @foreach ($field['options'] as $key => $option)
                            <div class="form-check mb-2 {{ !empty($field['switchType']) ? 'form-switch' : '' }}">
                                <label class="form-check-label"><input class="form-check-input" type="{{ $field['inputType'] }}" name="{{ $field['name'] }}[]" value="{{ $option }}" {{ ((is_array($value) && in_array($key, $value)) || $value == $key) ? 'checked' : '' }} {{ !empty($field['required']) ? 'required' : '' }}> {{ $option }}</label>
                            </div>
                        @endforeach
                    @else
                        <div class="form-check">
                            <label class="form-check-label"><input class="form-check-input" type="{{ $field['inputType'] }}" name="{{ $field['name'] }}" id="{{ $field['id'] }}" value="{{ $field['value'] }}" {{ boolval($value) ? 'checked' : '' }} {{ !empty($field['required']) ? 'required' : '' }}> {{ $field['displayValue'] }}</label>
                        </div>
                    @endif
                @elseif ($field['inputType'] == 'range')
                    <input class="form-range" type="range" min="{{ $field['min'] ?? '' }}" max="{{ $field['max'] ?? '' }}" step="{{ $field['step'] ?? '' }}" name="{{ $field['name'] }}" value="{{ $value }}" {{ !empty($field['required']) ? 'required' : '' }}>
                @elseif ($field['inputType'] == 'number')
                    <input class="form-control" type="number" min="{{ $field['min'] ?? '' }}" max="{{ $field['max'] ?? '' }}" step="{{ $field['step'] ?? '' }}" name="{{ $field['name'] }}" placeholder="{{ $field['placeholder'] }}" value="{{ $value }}" {{ !empty($field['required']) ? 'required' : '' }}>
                @elseif ($field['inputType'] == 'date')
                    @php
                        if ($value instanceof \Illuminate\Support\Carbon) {
                            $value = $value->format('Y-m-d');
                        }
                    @endphp
                    <input class="form-control" type="date" min="{{ $field['min'] ?? '' }}" max="{{ $field['max'] ?? '' }}" name="{{ $field['name'] }}" value="{{ $value }}" {{ !empty($field['required']) ? 'required' : '' }}>
                @elseif ($field['inputType'] === 'wysiwyg')
                    <textarea name="{{ $field['name'] }}" {{ !empty($field['required']) ? 'required' : '' }} class="tinymce">{{ $value }}</textarea>
                @elseif ($field['inputType'] == 'file')
                    @if (!$isCreate && !empty($field['show']) && !empty($value))
                        <br />
                        @if (!empty($field['multiple']) && is_array($value))
                            @foreach ($value as $img)
                                <img class="mb-2" src="{{ $img }}" height="100" width="100" style="object-fit: contain;" />
                            @endforeach
                        @else
                            <img class="mb-2" src="{{ $value }}" height="50" width="100" style="object-fit: contain;" />
                        @endif
                    @endif
                    <input class="form-control" type="file" name="{{ $field['name'] }}{{ !empty($field['multiple']) ? '[]' : '' }}" {{ !empty($field['multiple']) ? 'multiple' : '' }} accept="{{ $field['accept'] ?? '*' }}" {{ !empty($field['required']) ? 'required' : '' }}>
                @else
                    <input class="form-control" type="{{ $field['inputType'] }}" name="{{ $field['name'] }}" placeholder="{{ $field['placeholder'] }}" value="{{ $value }}" {{ !empty($field['required']) ? 'required' : '' }}>
                @endif
            </div>
        @endif
    @endforeach
</div>

@includeIf($routePrefix . '.' . $routeName . '.form-after')
