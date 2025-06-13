@extends('layouts.admin')

@section('title', 'Edit Branch')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Edit Branch</h1>
    <form action="{{ route('admin.branches.update', ['organization' => $organization->id, 'branch' => $branch->id]) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block mb-1 font-medium">Branch Name</label>
            <input type="text" name="name" value="{{ old('name', $branch->name) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Address</label>
            <input type="text" name="address" value="{{ old('address', $branch->address) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Opening Time</label>
            <input type="time" name="opening_time" value="{{ old('opening_time', $branch->opening_time) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Closing Time</label>
            <input type="time" name="closing_time" value="{{ old('closing_time', $branch->closing_time) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Total Capacity</label>
            <input type="number" name="total_capacity" value="{{ old('total_capacity', $branch->total_capacity) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Reservation Fee</label>
            <input type="number" name="reservation_fee" value="{{ old('reservation_fee', $branch->reservation_fee) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Cancellation Fee</label>
            <input type="number" name="cancellation_fee" value="{{ old('cancellation_fee', $branch->cancellation_fee) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Status</label>
            <select name="is_active" class="w-full border rounded px-3 py-2">
                <option value="1" {{ $branch->is_active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$branch->is_active ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Update Branch</button>
            <a href="{{ route('admin.branches.index', ['organization' => $organization->id]) }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection