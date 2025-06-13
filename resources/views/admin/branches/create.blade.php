@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Add Branch to {{ $organization->name }}</h1>
    <form action="{{ route('admin.branches.store', ['organization' => $organization->id]) }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-1 font-medium">Branch Name</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Address</label>
            <input type="text" name="address" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Phone</label>
            <input type="text" name="phone" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Opening Time</label>
            <input type="time" name="opening_time" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Closing Time</label>
            <input type="time" name="closing_time" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Total Capacity</label>
            <input type="number" name="total_capacity" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Reservation Fee</label>
            <input type="number" step="0.01" name="reservation_fee" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block mb-1 font-medium">Cancellation Fee</label>
            <input type="number" step="0.01" name="cancellation_fee" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Create Branch</button>
            @if(isset($organization))
                <a href="{{ route('admin.branches.index', ['organization' => $organization->id]) }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
            @else
                <a href="{{ route('admin.branches.global') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
            @endif
        </div>
    </form>
</div>
@endsection