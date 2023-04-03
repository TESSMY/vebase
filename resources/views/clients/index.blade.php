@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <span class="page-title h4">Client List</span>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            <div class="row mb-3">
                <div class="col-md-8">
                    <a href="{{ route('admin.clients.create') }}" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-primary rounded"><i class="uil-plus-circle"></i> Add Client </a>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-secondary rounded my-1" data-bs-toggle="modal" data-bs-target="#importModal">Import</button>
                    <form action="{{ route('admin.clients.export') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <button type="submit" class="col-12 col-md-2 mb-3 mb-md-0 btn btn-secondary rounded my-1">Export</button>
                    </form>
                </div>
            </div>

            <!-- Modal -->
            <form action="{{ route('admin.clients.import') }}" method="POST" enctype="multipart/form-data">
                <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h1 class="modal-title fs-5" id="importModalLabel">Import Clients</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="file" name="import_file" class="form-control" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                    </div>
                </div>
            </form>

            <form action="#" method="GET" id="form">
                @csrf
                <div class="row mb-3">
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">Display:</span>
                        <select class="form-select mx-1" name="limit">
                            <option selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="my-auto">Clients</span>
                    </div>
                    <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex">
                        <span class="my-auto">Filter:</span>
                        <select class="form-select mx-1" name="filter">
                            <option selected>Choose...</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div class="col-md-4"></div>
                    <div class="col-12 col-md-4 p-0">
                        <div class="px-2">
                            <label class="form-label my-auto me-md-2">Search: </label>
                            <input class="form-control" type="search" placeholder="Search" name="search" value="{{ request()->input('search') }}">
                        </div>
                    </div>
                </div>
            </form>
            <div class="overflow-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <td>Company Name</td>
                            <td>Name</td>
                            <td>Phone</td>
                            <td>Email</td>
                            <td>Created Date</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr>
                                <td>{{ $client->company_name }}</td>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->phone }}</td>
                                <td>{{ $client->email }}</td>
                                <td>{{ $client->created_at }}</td>
                                <td>
                                    <a href="{{ route('admin.clients.edit', [$client->getRouteKey()]) }}"><button type="button" class="btn btn-primary">Edit</button></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center">There are no clients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2">
            {{ $clients->links() }}
        </div>
    </div>
@endsection
