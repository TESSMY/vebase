<div class="row">
    <div class="col-12 col-md-6 mb-md-0 mb-2">
        <label>Upload Image</label>
        <input type="file" class="form-control" >
    </div>
    <div class="col-12 col-md-6">
        <div class="row">
            <div class="col-12 mb-md-2 mb-2">
                <label>Name</label>
                <input class="form-control" type="text" name="name" placeholder="Name" value="{{ old('name') ?? (!empty($admin) ? $admin->name : '') }}" required>
            </div>
            <div class="col-12 mb-md-2 mb-2">
                <label>Email</label>
                <input class="form-control" type="email" name="email" placeholder="Email" value="{{ old('email') ?? (!empty($admin) ? $admin->email : '') }}" required>
            </div>
        </div>
    </div>
</div>
<div class="row mb-2">
    <div class="col-12 col-md-6 mb-md-0 mb-2">
        <label>Telephone</label>
        <input class="form-control" type="text" placeholder="Telephone" name="phone" value="{{ old('phone') ?? (!empty($admin) ? $admin->phone : '') }}">
    </div>
    <div class="col-12 col-md-6">
        <label>Role</label>
        <select class="form-select" name="role">
            <option selected>-- Role --</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row mb-2">
    <div class="col-12 col-md-6 mb-md-0 mb-2">
        <label>Password</label>
        <input class="form-control" type="text" placeholder="Password" name="password">
    </div>
    <div class="col-12 col-md-6 mb-md-0 mb-2">
        <label>Confirm Password</label>
        <input class="form-control" type="text" placeholder="Confirm Password" name="password_confirmation">
    </div>
</div>
<div class="row col-12">
    <button type="submit" class="col-12 col-md-1 btn btn-success m-2">{{ request()->routeIs('admin.admins.create') ? 'Create' : 'Update' }}</button>
    <a href="{{ route('admin.admins.index') }}" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
</div>