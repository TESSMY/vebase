@extends('layouts/layout')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <span class="page-title h4">{{ $modelName }} History</span>
                </div>
            </div>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="overflow-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventoryReports as $inventoryReport)
                        <tr>
                           <td>{{ $inventoryReport->created_by->name }}</td>
                           <td>{{ $inventoryReport->created_at }}</td>
                           <td><a href="{{ $inventoryReport->file_url }}" download class="btn btn-primary">Download</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="text-center">There are no {{ $modelName }} history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">
            {{ $inventoryReports->links() }}
        </div>
    </div>
@endsection