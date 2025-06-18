@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Add Branch to {{ $organization->name }}</h1>
        <a href="{{ route('admin.branches.index', ['organization' => $organization->id]) }}"
           class="inline-block bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 transition font-semibold">
            ← Back to Branches
        </a>
    </div>
    <form action="{{ route('admin.branches.store', ['organization' => $organization->id]) }}" method="POST" class="space-y-6 bg-white rounded-2xl shadow p-8">
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block mb-1 font-medium">Opening Time</label>
                <input type="time" name="opening_time" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block mb-1 font-medium">Closing Time</label>
                <input type="time" name="closing_time" class="w-full border rounded px-3 py-2" required>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block mb-1 font-medium">Total Capacity</label>
                <input type="number" name="total_capacity" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block mb-1 font-medium">Reservation Fee</label>
                <input type="number" step="0.01" name="reservation_fee" class="w-full border rounded px-3 py-2" required>
            </div>
        </div>
        <div>
            <label class="block mb-1 font-medium">Cancellation Fee</label>
            <input type="number" step="0.01" name="cancellation_fee" class="w-full border rounded px-3 py-2" required>
        </div>

        @if(!$isHeadOffice)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block mb-1 font-medium">Contact Person</label>
                <input type="text" name="contact_person" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block mb-1 font-medium">Contact Person Designation</label>
                <input type="text" name="contact_person_designation" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block mb-1 font-medium">Contact Person Phone</label>
                <input type="text" name="contact_person_phone" class="w-full border rounded px-3 py-2" required>
            </div>
        </div>
        @else
        <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded">
            <strong>Head Office:</strong> Contact person details will be set to the organization's contact person.
        </div>
        @endif

        <div class="flex gap-3 pt-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Create Branch</button>
            <a href="{{ route('admin.branches.index', ['organization' => $organization->id]) }}" class="text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection