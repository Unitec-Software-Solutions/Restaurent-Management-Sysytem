@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Item List</h2>
        <a href="{{ route('frontend.items.create') }}" class="btn btn-primary">+ Add New Item</a>
    </div>

    @foreach($groupedItems as $categoryName => $items)
        <div class="category-section mb-5">
            <h3 class="category-title mb-4">{{ $categoryName }}</h3>
            <div class="row">
                @foreach($items as $item)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $item->name }}</h5>
                                <p class="card-text">Price: ${{ number_format($item->selling_price, 2) }}</p>
                                <a href="{{ route('frontend.items.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<style>
    .category-section {
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }
    .category-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
    }
    .card {
        height: 100%;
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
</style>
@endsection 