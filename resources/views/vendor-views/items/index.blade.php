@extends('layouts.vendor.app') <!-- Assuming you have a vendor-specific layout -->

@section('title', 'Item List')

@section('content')
<div class="content container-fluid">
    @if(Session::has('success'))
    <div class="alert alert-success">
        {{ Session::get('success') }}
    </div>
@endif
    <!-- Page content goes here -->
    <h1>Item List</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Number</th> <!-- Add a new column for item number -->
                <th>Food ID</th>
                <th>Item Name</th> <!-- Assuming you want to display supplier name -->
                <th>Description</th>
                <th>Action</th> <!-- Add a new column for actions -->
                <!-- Add other table headers as needed -->
            </tr>
        </thead>
        <tbody>
            @php
                $itemNumber = 1; // Initialize a counter variable
            @endphp
            @foreach($items as $item)
                <tr>
                    <td>{{ $itemNumber }}</td> <!-- Display the item number -->
                    <td>{{ $item->food_name }}</td>
                    <td>{{ $item->suppliers_name }}</td>
                    <td>{{ $item->description }}</td>
                  
                    <td>
                        <a href="{{ route('vendor.item.edit', ['item' => $item->id]) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('vendor.item.destroy', ['item' => $item->id]) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                    <!-- Add other table cells with item data -->
                </tr>
                @php
                    $itemNumber++; // Increment the counter for the next item
                @endphp
            @endforeach
        </tbody>
    </table>
</div>
@endsection
