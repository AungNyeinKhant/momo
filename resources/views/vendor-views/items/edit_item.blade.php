@extends('layouts.vendor.app') <!-- Assuming you have a vendor-specific layout -->

@section('title', 'Edit Item')

@section('content')
<div class="content container-fluid">
    <!-- Page content goes here -->
    <h1>Edit Item</h1>
    <form method="POST" action="{{ route('vendor.item.update', ['item' => $item->id]) }}">
        @csrf
        @method('PUT') <!-- Use the PUT method for updating -->

        <div class="form-group">
            <label for="food_id">Food ID:</label>
            <select name="food_id" id="food_id" class="form-control" required>
                <option value="" disabled>Select Food</option>
                @foreach($foodList as $food)
                    <option value="{{ $food->id }}" {{ $food->id == $item->food_id ? 'selected' : '' }}>
                        {{ $food->name }}
                    </option>
                @endforeach
            </select>
        

        <div class="form-group">
            <label for="supplier_id">Item Name:</label>
            <select name="suppliers_id" id="suppliers_id" class="form-control" required>
                <option value="" disabled>Select Supplier</option>
                @foreach($itemList as $supplier)
                    <option value="{{ $supplier->id }}" {{ $supplier->id == $item->suppliers_id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description" id="description" class="form-control" required>{{ $item->description }}</textarea>
        </div>

        

        <!-- Add other fields for editing item details -->

        <button type="submit" class="btn btn-primary">Update Item</button>
    </form>
</div>
@endsection
