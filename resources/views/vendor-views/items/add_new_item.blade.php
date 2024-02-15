@extends('layouts.vendor.app')

@section('title', translate('messages.dashboard'))

@section('content')
<div class="content container-fluid">
    @if(auth('vendor')->check())
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h1 class="page-header-title my-1">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <!-- Add any content specific to your dashboard here -->
                </div>
                Add Item
            </h1>
            <!-- Add any other components or elements specific to your dashboard here -->
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Add the main content of your dashboard here -->
    <div class="row">
        <div class="col-12">
            <!-- Example: Display some data or charts -->
            <div class="card">
                <div class="card-body">
                    <!-- Add your dashboard content here -->
                    <form method="POST" action="{{ route('vendor.item.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="food_id">Select Food:</label>
                            <select name="food_id" id="food_id" class="form-control" required>
                                <option value="">Select Food</option>
                                @foreach($foodList as $food)
                                    <option value="{{ $food->id }}">{{ $food->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="item_id">Select Item:</label>
                            <select name="suppliers_id" id="suppliers_id" class="form-control" required>
                                <option value="">Select Item</option>
                                @foreach($itemList as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea name="description" id="description" class="form-control" required></textarea>
                        </div>

                        <!-- Add Stock Quantity Field -->
                        

                        <button type="submit" class="btn btn-primary">Add Item</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
</div>
@endsection
