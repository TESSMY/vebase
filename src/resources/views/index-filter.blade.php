@if (! empty($model->filters))
    @foreach ($model->filters as $filter)
        @php
            $field['displayName'] = $filter['displayName'] ?? ucwords(str_replace('_', ' ', $filter['name']));
            $value = request($filter['name']);
            if (!empty($filter['class'])) {
                $data = $filter['class']::query();
                if (!empty($filter['where'])) {
                    foreach ($filter['where'] as $condition) {
                        $data->where($condition[0], $condition[1], $condition[2]);
                    }
                }
                if (!empty($filter['order'])) {
                    foreach ($filter['order'] as $order) {
                        $data->orderBy($order[0], $order[1]);
                    }
                }
                if (!empty($filter['columns'])) {
                    $data = $data->get($filter['columns']);
                } else {
                    $data = $data->get();
                }
                $filter['options'] = [];
                foreach ($data as $item) {
                    $filter['options'][$item[$filter['key'] ?? 'id']] = $item[$filter['value'] ?? 'name'];
                }
            }
        @endphp
        <div class="col-6 {{ $filter['size'] ?? 'col-md-auto' }} mt-2 px-2">
            <div class="row">
                @if (!empty($filter['displayName']))
                    <div class="col-auto">
                        <label class="col-form-label">{{ $filter['displayName'] }}</label>
                    </div>
                @endif
                <div class="col-auto">
                    <select class="form-select" name="{{ $filter['name'] }}" {{ !empty($filter['required']) ? 'required' : '' }} onchange="this.form.submit()">
                        @if (!empty($filter['includeEmpty']))
                            <option value="">All</option>
                        @endif
                        @foreach ($filter['options'] as $key => $option)
                            <option value="{{ $key }}" {{ !is_null($value) && $value == $key ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endforeach
@endif
