@if (! empty($model->filters))
    @foreach ($model->filters as $filter)
        @php
            $field['displayName'] = $filter['displayName'] ?? ucwords(str_replace('_', ' ', $filter['name']));
            $isCreate = request()->routeIs('*.create');
            $value = old($filter['name'], request($filter['name'])) ?? (!$isCreate && !empty($$routeModel) ? $$routeModel[$filter['name']] ?? '' : ($filter['default'] ?? ''));
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
        <div class="col-12 {{ $filter['size'] ?? 'col-md-6' }} my-md-2 my-2">
            <div class="row">
                @if (!empty($filter['displayName']))
                    <div class="col-auto">
                        <label class="col-form-label">{{ $filter['displayName'] }}</label>
                    </div>
                @endif
                <div class="col-auto">
                    <select class="form-select" name="{{ $filter['name'] }}" {{ !empty($filter['required']) ? 'required' : '' }} onchange="this.form.submit()">
                        @if (!empty($filter['includeEmpty']))
                            <option value="">N/A</option>
                        @endif
                        @foreach ($filter['options'] as $key => $option)

                            <option value="{{ $key }}" {{ $value == $key ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endforeach
@endif