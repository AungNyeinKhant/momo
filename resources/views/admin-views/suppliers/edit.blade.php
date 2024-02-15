@extends('layouts.admin.app')

@section('title', 'Edit Supplier')

@section('content')
    <div class="content container-fluid">
        <!-- Page content goes here -->
        <h1>Edit Supplier</h1>
        <form method="POST" action="{{ route('admin.supplier.update', ['id' => $supplier->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Supplier Name -->
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $supplier->name) }}" required>
                @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <!-- Supplier Link -->
            <div class="form-group">
                <label for="link">Link</label>
                <input type="url" class="form-control @error('link') is-invalid @enderror" id="link" name="link" value="{{ old('link', $supplier->link) }}" required>
                @error('link')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <!-- Supplier Image -->
            <div class="form-group">
                <label for="image">Supplier Image</label>
                <input type="file" class="form-control-file @error('image') is-invalid @enderror" id="image" name="image">
                @error('image')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Update Supplier</button>
        </form>
    </div>
@endsection
