<div class="row">
    <div class="col-12 col-md-6">
        <div class="row">
            <div class="col-12 mb-md-0 mb-2">
                <label for="exampleDataList" class="form-label">Datalist example</label>
                <input class="form-control" list="datalistOptions" id="exampleDataList" placeholder="Type to search...">
                    <datalist id="datalistOptions">
                    <option value="San Francisco">
                    <option value="New York">
                    <option value="Seattle">
                    <option value="Los Angeles">
                    <option value="Chicago">
                </datalist>
            </div>
            <div class="col-12 mb-md-2 mb-2">
                <label>Date</label>
                <input class="form-control" type="text" name="name" placeholder="Name" value="{{ old('name') ?? (!empty($admin) ? $admin->name : '') }}" required>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="row">
            <div class="col-12 mb-md-2 mb-2">
                <label>Date</label>
                <input class="form-control" type="text" name="name" placeholder="Name" value="{{ old('name') ?? (!empty($admin) ? $admin->name : '') }}" required>
            </div>
            <div class="col-12 mb-md-2 mb-2">
                <label>Payment Term</label>
                <input class="form-control" type="email" name="email" placeholder="Email" value="{{ old('email') ?? (!empty($admin) ? $admin->email : '') }}" required>
            </div>
        </div>
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