@extends('layouts.vendor.app') <!-- Assuming you have a vendor-specific layout -->

@section('title', 'Supplier List')

@section('content')
    <div class="content container-fluid">
        <!-- Page content goes here -->
        <h1>Supplier List</h1>
        <div class="row">
            @foreach($suppliers as $supplier)
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <a href="{{ $supplier->link }}" target="_blank">
                            <img src="{{ asset('storage/app/public/' . $supplier->image) }}" alt="Supplier Image" class="img-fluid" width="200px">
                        </a>
                        <p>{{ $supplier->name }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
