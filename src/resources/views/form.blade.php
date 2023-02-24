{{-- <div class="row">
    <div class="col-12 col-md-6 mb-md-0 mb-2">
        <label>Upload Image</label>
        <input type="file" class="form-control" >
    </div>
    <div class="col-12 col-md-6">
        <div class="row">
            <div class="col-12 mb-md-2 mb-2">
                <label>Name</label>
                <input class="form-control" type="text" name="name" placeholder="Name" value="{{ old('name') ?? (!empty($model) ? $model->name : '') }}" required>
            </div>
            <div class="col-12 mb-md-2 mb-2">
                <label>Email</label>
                <input class="form-control" type="email" name="email" placeholder="Email" value="{{ old('email') ?? (!empty($model) ? $model->email : '') }}" required>
            </div>
        </div>
    </div>
</div> --}}
{{-- <div class="row">
    <div class="col-12 col-md-6 mb-md-2 mb-2">
        <label>Name</label>
        <input class="form-control" type="text" name="name" placeholder="Name" value="{{ old('name') ?? (!empty($model) ? $model->name : '') }}" required>
    </div>
    <div class="col-12 col-md-6 mb-md-2 mb-2">
        <label>Email</label>
        <input class="form-control" type="email" name="email" placeholder="Email" value="{{ old('email') ?? (!empty($model) ? $model->email : '') }}" required>
    </div>
    <div class="col-12 col-md-6 mb-md-2 mb-2">
        <label>Email</label>
        <input class="form-control" type="email" name="email" placeholder="Email" value="{{ old('email') ?? (!empty($model) ? $model->email : '') }}" required>
    </div>
</div> --}}
<div class="row">
    @foreach ($model::createFields as $createField)
        <div class="col-12 col-md-6 mb-md-2 mb-2">
            <label>{{ $createField['name'] }}</label>
            <input class="form-control" type="{{ $createField['type'] }}" name="name" placeholder="{{ $createField['name'] }}" value="{{ old($createField['name']) ?? (!empty($model) ? $model[$createField['name']] : '') }}" {{ $createField['required'] }}>
        </div>
    @endforeach
</div>
<div class="row col-12">
    <button type="submit" class="col-12 col-md-1 btn btn-success m-2">{{ request()->routeIs($routePrefix . '.' . $routeName . '.create') ? 'Create' : 'Update' }}</button>
    <a href="{{ route($routePrefix . '.' . $routeName . '.index') }}" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
</div>