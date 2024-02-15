@extends('layouts.admin.app')

@section('title', 'Supplier List')

@section('content')
    <div class="content container-fluid">
        @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
        <!-- Page content goes here -->
        <h1>Supplier Item List</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Link</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->id }}</td>
                    <td>
                        <img src="{{ asset('storage/app/public/' . $supplier->image) }}" alt="Supplier Image" width="100" height="100">
                    </td>
                    <td>{{ $supplier->name }}</td>
                    <td><a href="{{ $supplier->link }}" target="_blank">{{ $supplier->link }}</a></td>
                    <td>
                        <a href="{{ route('admin.supplier.edit', $supplier->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('admin.supplier.delete', $supplier->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
