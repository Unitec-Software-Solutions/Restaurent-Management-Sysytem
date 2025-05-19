@extends('layouts.main')

@section('title', 'Item List')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Items</h2>
        <a href="{{ route('admin.inventory.items.create') }}" class="btn btn-primary">Add New Item</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Buying Price</th>
                            <th>Selling Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><img src="{{ $item->image_url }}" width="50" alt=""></td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->item_code }}</td>
                                <td>{{ $item->category?->name ?? '-' }}</td>

                                <td>{{ number_format($item->buying_price, 2) }}</td>
                                <td>{{ number_format($item->selling_price, 2) }}</td>
                                <td>
                                    @if (!$item->deleted_at)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Archived</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.inventory.items.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('admin.inventory.items.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection