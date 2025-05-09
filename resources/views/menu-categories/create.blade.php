@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Menu Category</h1>
    <form action="{{ route('menu-categories.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <!-- Display Food Items by Category -->
        <h3>Food Items</h3>
        @foreach ($foodItemsByCategory as $categoryId => $items)
            <h4>Category: {{ $categoryId }}</h4>
            <ul>
                @foreach ($items as $item)
                    <li>
                        <input type="checkbox" name="food_items[]" value="{{ $item->id }}">
                        {{ $item->name }} - ${{ $item->price }}
                    </li>
                @endforeach
            </ul>
        @endforeach

        <!-- Display Menu Items by Category -->
        <h3>Menu Items</h3>
        @foreach ($menuItemsByCategory as $categoryId => $items)
            <h4>Category: {{ $categoryId }}</h4>
            <ul>
                @foreach ($items as $item)
                    <li>
                        <input type="checkbox" name="menu_items[]" value="{{ $item->id }}">
                        {{ $item->name }} - ${{ $item->price }}
                    </li>
                @endforeach
            </ul>
        @endforeach

        <button type="submit" class="btn btn-primary mt-3">Add Category</button>
    </form>

    @if (session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif
</div>
@endsection 