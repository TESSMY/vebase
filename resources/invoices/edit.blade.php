@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Edit Admin</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                  <li class="breadcrumb-item"><a href="{{ route('admin.admins.index') }}">Admin</a></li>
                  <li class="breadcrumb-item active">Edit Admin</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <form action="{{ route('admin.admins.update', $admin->getRouteKey()) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row border-bottom mb-2">
                    <span class="h5">Information</span>
                </div>
                @include('admin.admins.form')
            </form>
        </div>
    </div>
@endsection
