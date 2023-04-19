@extends('layouts/layout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="page-title-box">
                    <span class="page-title h4">Edit Quotation</span>
                </div>
            </div>
            <nav class="col-12 col-md-6">
                <ol class="breadcrumb d-md-flex justify-content-md-end my-auto">
                    <li class="breadcrumb-item"><a href="{{ route('admin.quotations.edit', [$quotation->getRouteKey()]) }}">Quotation Request</a></li>
                    <li class="breadcrumb-item active">Edit Quotation</li>
                </ol>
            </nav>
        </div>
        <div class="border my-2 mb-3"></div>
        <div class="bg-white card shadow py-3 px-4">
            @can('update', $quotation)
                <form action="{{ route('admin.quotations.update', [$quotation->getRouteKey()]) }}" method="POST" enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <quotation-form :quotation="{{ $quotation }}" :quotation-items="{{ $quotation->quotationItems }}" :quotation-client="{{ $quotation->client }}"></quotation-form>
                    <div class="mt-3">
                        @if($quotation->status == \App\Models\Quotation::STATUS_PENDING)
                            <button class="btn btn-primary me-2" type="submit">{{  __('Update') }}</button>
                            <button class="btn btn-secondary">{{ __('Close') }}</button>
                        @endif
                    </div>
                </form>
            @endcan
            <hr>
            @can('delete', $quotation)
                @if($quotation->status == \App\Models\Quotation::STATUS_PENDING)
                    <form action="{{ route('admin.quotations.destroy', $quotation) }}" method="POST" enctype="multipart/form-data">
                        @method('DELETE')
                        @csrf
                        <div class="text-end">
                            <button class="btn btn-danger px-2" type="submit">
                                Delete
                            </button>
                        </div>
                    </form>
                @endif
            @endcan
            @can('update', $quotation)
                @if ($quotation->status == \App\Models\Quotation::STATUS_APPROVED && !empty($quotation->salesOrder) && ($quotation->salesOrder->status == \App\Models\SalesOrder::STATUS_DRAFT || $quotation->salesOrder->status == \App\Models\SalesOrder::STATUS_OUTSTANDING))
                    <form action="{{ route('admin.quotations.void', $quotation) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="text-end">
                            <button class="btn btn-danger px-2" type="submit">
                                Void
                            </button>
                        </div>
                    </form>
                @endif
            @endcan
        </div>
    </div>
@endsection
