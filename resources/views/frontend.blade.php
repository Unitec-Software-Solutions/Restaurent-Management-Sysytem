@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Menu</h1>

    @foreach($groupedItems as $categoryName => $items)
        <div class="category-section mb-5">
            <h3 class="category-title mb-4">{{ $categoryName }}</h3>
            <div class="row">
                @foreach($items as $item)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" class="card-img-top" alt="{{ $item->name }}">
                            @else
                                <img src="{{ asset('images/default-item.jpg') }}" class="card-img-top" alt="Default Image">
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">{{ $item->name }}</h5>
                                <p class="card-text">Price: ${{ number_format($item->selling_price, 2) }}</p>
                                @auth
                                    <div class="admin-actions">
                                        <a href="{{ route('frontend.items.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('frontend.items.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection

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