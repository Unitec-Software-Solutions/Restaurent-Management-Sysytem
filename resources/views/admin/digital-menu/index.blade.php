@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
            <!-- Sidebar content -->
        </div>

        <!-- Main Content -->
        <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Digital Menu</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('admin.digital-menu.create') }}" class="btn btn-primary">Add Menu Item</a>
                </div>
            </div>

            <!-- Display Grouped Items -->
            @foreach($groupedItems as $categoryName => $items)
                <div class="mb-5">
                    <h4 class="mb-3">{{ $categoryName }}</h4>
                    <div class="row">
                        @foreach($items as $item)
                            <div class="col-md-3 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $item->item_name }}</h5>
                                        <p class="card-text">Rs. {{ number_format($item->selling_price, 2) }}</p>
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('admin.digital-menu.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form action="{{ route('admin.digital-menu.destroy', $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection