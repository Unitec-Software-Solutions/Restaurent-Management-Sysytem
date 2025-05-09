@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Our Menu</h1>
    @foreach ($menuCategories as $category)
        <div class="category">
            <h2>{{ $category->name }}</h2>
            <p>{{ $category->description }}</p>
            <ul>
                @foreach ($category->foodItems as $item)
                    <li>
                        <h3>{{ $item->name }}</h3>
                        <p>{{ $item->description }}</p>
                        <p>Price: ${{ $item->price }}</p>
                    </li>
                @endforeach
                @foreach ($category->menuItems as $item)
                    <li>
                        <h3>{{ $item->name }}</h3>
                        <p>{{ $item->description }}</p>
                        <p>Price: ${{ $item->price }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
@endsection 