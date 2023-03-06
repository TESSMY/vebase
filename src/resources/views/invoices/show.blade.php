@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">View Admin</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                  <li class="breadcrumb-item"><a href="{{ route('admin.admins.index') }}">Admin</a></li>
                  <li class="breadcrumb-item active">View Admin</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row border-bottom mb-2">
                <span class="h5">Information</span>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 mb-md-0 mb-2">
                    <label>Upload Image</label>
                    <input type="file" class="form-control" readonly>
                </div>
                <div class="col-12 col-md-6">
                    <div class="row">
                        <div class="col-12 mb-md-2 mb-2">
                            <label>Name</label>
                            <input class="form-control" type="text" name="name" placeholder="Name" value="{{ old('name') ?? !empty($admin) ? $admin->name : '' }}" readonly>
                        </div>
                        <div class="col-12 mb-md-2 mb-2">
                            <label>Email</label>
                            <input class="form-control" type="email" name="email" placeholder="Email" value="{{ old('email') ?? !empty($admin) ? $admin->email : '' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-12 col-md-6 mb-md-0 mb-2">
                    <label>Telephone</label>
                    <input class="form-control" type="text" placeholder="Telephone" name="phone" value="{{ old('phone') ?? !empty($admin) ? $admin->phone : '' }}" readonly>
                </div>
                <div class="col-12 col-md-6">
                    <label>Role</label>
                    <select class="form-select" name="role" readonly>
                        <option selected>{{ $admin->getRoleNames()->first() }}</option>
                    </select>
                </div>
            </div>
            <div class="row col-12">
                <a href="{{ route('admin.admins.edit', $admin->getRouteKey()) }}" class="col-12 col-md-1 btn btn-success m-2">Edit</a>
                <a href="{{ route('admin.admins.index') }}" class="col-12 col-md-1 btn btn-dark m-2">Back</a>
                @if (isset($admin) && Auth::user()->email != $admin->email)
                    <form action="{{ route('admin.admins.destroy', [$admin->getRouteKey()]) }}" class="col-12 col-md-1 m-2 px-0" method="POST">
                        @method('DELETE')
                        @csrf
                        <button class="col-12 btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete? You cannot revert this.')">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
