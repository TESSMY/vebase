@if (! empty($model->filters))
    @foreach ($model->filters as $filter)
        @php
            // The default was written to $field, a variable nothing here reads, so a filter
            // without an explicit displayName rendered with no label at all.
            $filter['displayName'] = $filter['displayName'] ?? ucwords(str_replace('_', ' ', $filter['name']));
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

            // A filter declared with neither `class` nor `options` used to fatal on the loop below.
            $options = $filter['options'] ?? [];
        @endphp
        <div class="col-6 {{ $filter['size'] ?? 'col-md-auto' }} mt-2 px-2">
            <div class="row">
                @if (!empty($filter['displayName']))
                    <div class="col-auto">
                        <label class="col-form-label">{{ $filter['displayName'] }}</label>
                    </div>
                @endif
                <div class="col-auto">
                    {{-- v-pre: option labels are stored values (see common/table.blade.php). --}}
                    <select v-pre class="form-select" name="{{ $filter['name'] }}" {{ !empty($filter['required']) ? 'required' : '' }} onchange="this.form.submit()">
                        @if (!empty($filter['includeEmpty']))
                            <option value="">All</option>
                        @endif
                        @foreach ($options as $key => $option)
                            <option value="{{ $key }}" {{ !is_null($value) && $value == $key ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endforeach
@endif
