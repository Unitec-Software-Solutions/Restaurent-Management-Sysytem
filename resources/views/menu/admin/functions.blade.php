@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Admin Functions</h1>
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
                        <a href="{{ route('menu.admin.edit', $item->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('menu.admin.delete', $item->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </li>
                @endforeach
                @foreach ($category->menuItems as $item)
                    <li>
                        <h3>{{ $item->name }}</h3>
                        <p>{{ $item->description }}</p>
                        <p>Price: ${{ $item->price }}</p>
                        <a href="{{ route('menu.admin.edit', $item->id) }}" class="btn btn-primary">Edit</a>
                        <form action="{{ route('menu.admin.delete', $item->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
@endsection 