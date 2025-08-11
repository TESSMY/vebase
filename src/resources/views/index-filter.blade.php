@if (! empty($model->filters))
    <div class="row gx-2 d-md-flex flex-wrap">
        @foreach ($model->filters as $filter)
            @php
                $field['displayName'] = $filter['displayName'] ?? ucwords(str_replace('_', ' ', $filter['name']));
                $isCreate = request()->routeIs('*.create');
                $value = old($filter['name']) ?? (!$isCreate && !empty($$routeModel) ? $$routeModel[$filter['name']] ?? '' : ($filter['default'] ?? ''));
                if (!empty($filter['class'])) {
                    $data = $filter['class']::query();
                    if (!empty($filter['where'])) {
                        foreach ($filter['where'] as $condition) {
                            $data->where($condition[0], $condition[1], $condition[2]);
                        }
                    }
                    $data = $data->get();
                    $filter['options'] = [];
                    foreach ($data as $item) {
                        $filter['options'][$item[$filter['key'] ?? 'id']] = $item[$filter['value'] ?? 'name'];
                    }
                }
            @endphp
            <div class="col-12 {{ $filter['size'] ?? 'col-md-6' }} mb-md-2 mb-2">
                @if (!empty($filter['displayName']))
                    <label class="col-form-label">{{ $filter['displayName'] }}</label>
                @endif
                <select class="form-select" name="{{ $filter['name'] }}" {{ !empty($filter['required']) ? 'required' : '' }} onchange="this.form.submit()">
                    @if (!empty($filter['includeEmpty']))
                        <option value="">N/A</option>
                    @endif
                    @foreach ($filter['options'] as $key => $option)
                        <option value="{{ $key }}" {{ $value == $key ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
@endif