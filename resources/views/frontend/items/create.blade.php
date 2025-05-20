@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create New Item</h2>
    <form action="{{ route('frontend.items.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="item_category_id" class="form-control" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Selling Price</label>
            <input type="number" step="0.01" name="selling_price" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Create Item</button>
    </form>
</div>
@endsection 