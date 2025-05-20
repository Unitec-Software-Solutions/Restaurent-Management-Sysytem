@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Item</h2>
    <form action="{{ route('frontend.items.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="item_category_id" class="form-control" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $item->item_category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Selling Price</label>
            <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ $item->selling_price }}" required>
        </div>
        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" class="form-control">
            @if($item->image)
                <img src="{{ asset('storage/' . $item->image) }}" alt="Current Image" class="img-thumbnail mt-2" style="max-width: 200px;">
            @endif
        </div>
        <button type="submit" class="btn btn-primary">Update Item</button>
    </form>
</div>
@endsection 