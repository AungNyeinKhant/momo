@extends('layouts.admin.app')

@section('title', translate('Add New Supplier'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('public/assets/admin/css/tags-input.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> {{ translate('messages.add') }}
                        {{ translate('messages.new') }} {{ translate('messages.supplier') }}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <form action="{{ route('admin.supplier.save') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="image">{{ translate('Image') }}</label>
                <input type="file" name="image" id="image" class="form-control-file" accept="image/*">
            </div>
            <div class="row g-2">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="name">{{ translate('Item Name') }}</label>
                        <input type="text" name="name" id="name" class="form-control"    placeholder="{{ translate('messages.new_item_name') }}" required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="link">{{ translate('Supplier Link') }}</label>
                        <input type="text" name="link" id="link" class="form-control"   placeholder="{{ translate('messages.add_link') }}" required>
                    </div>
                </div>
            </div>

            <!-- Add your other form fields for the supplier here -->
            
            <button type="submit" class="btn btn-primary">{{ translate('Save Supplier') }}</button>
        </form>
    </div>
@endsection