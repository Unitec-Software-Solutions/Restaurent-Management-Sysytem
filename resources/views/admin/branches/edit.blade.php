@extends('layouts.admin')

@section('title', 'Edit Branch')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Edit Branch</h1>
        <a href="{{ route('admin.branches.index', ['organization' => $organization->id]) }}"
           class="inline-block bg-gray-200 text-gray-800 px-5 py-2 rounded hover:bg-gray-300 transition font-semibold">
            ← Back to Branches
        </a>
    </div>
    @php
        $isHeadOffice = $branch->id == optional($organization->branches->sortBy('id')->first())->id;
    @endphp
    <form action="{{ route('admin.branches.update', ['organization' => $organization->id, 'branch' => $branch->id]) }}" method="POST" class="space-y-6 bg-white rounded-2xl shadow p-8">
        @csrf
        @method('PUT')
        <div>
            <label class="block mb-1 font-medium">Branch Name</label>
            <input type="text" name="name" value="{{ old('name', $branch->name) }}" placeholder="e.g. Main Branch" class="w-full border rounded px-3 py-2"
                {{ auth('admin')->user()->isSuperAdmin() ? '' : 'readonly' }}>
            @error('name')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <label class="block mb-1 font-medium">Address</label>
            <input type="text" name="address" value="{{ old('address', $branch->address) }}" placeholder="e.g. 123 Main St, City" class="w-full border rounded px-3 py-2"
                {{ (auth('admin')->user()->isSuperAdmin() || auth('admin')->user()->isOrganizationAdmin() || auth('admin')->user()->isBranchAdmin()) ? '' : 'readonly' }}>
            @error('address')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <label class="block mb-1 font-medium">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" placeholder="e.g. 0712345678" class="w-full border rounded px-3 py-2"
                {{ (auth('admin')->user()->isSuperAdmin() || auth('admin')->user()->isOrganizationAdmin() || auth('admin')->user()->isBranchAdmin()) ? '' : 'readonly' }}>
            @error('phone')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block mb-1 font-medium">Contact Person</label>
                <input type="text" name="contact_person"
                    value="{{ old('contact_person', $isHeadOffice ? $organization->contact_person : $branch->contact_person) }}"
                    placeholder="e.g. John Doe"
                    class="w-full border rounded px-3 py-2"
                    {{ (auth('admin')->user()->isSuperAdmin() || auth('admin')->user()->isOrganizationAdmin() || auth('admin')->user()->isBranchAdmin()) ? '' : 'readonly' }}>
                @error('contact_person')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block mb-1 font-medium">Contact Person Designation</label>
                <input type="text" name="contact_person_designation"
                    value="{{ old('contact_person_designation', $isHeadOffice ? $organization->contact_person_designation : $branch->contact_person_designation) }}"
                    placeholder="e.g. Manager"
                    class="w-full border rounded px-3 py-2"
                    {{ (auth('admin')->user()->isSuperAdmin() || auth('admin')->user()->isOrganizationAdmin() || auth('admin')->user()->isBranchAdmin()) ? '' : 'readonly' }}>
                @error('contact_person_designation')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block mb-1 font-medium">Contact Person Phone</label>
                <input type="text" name="contact_person_phone"
                    value="{{ old('contact_person_phone', $isHeadOffice ? $organization->contact_person_phone : $branch->contact_person_phone) }}"
                    placeholder="e.g. 0712345678"
                    class="w-full border rounded px-3 py-2"
                    {{ (auth('admin')->user()->isSuperAdmin() || auth('admin')->user()->isOrganizationAdmin() || auth('admin')->user()->isBranchAdmin()) ? '' : 'readonly' }}>
                @error('contact_person_phone')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block mb-1 font-medium">Opening Time</label>
                <input type="time" name="opening_time" value="{{ old('opening_time', \Carbon\Carbon::parse($branch->opening_time)->format('H:i')) }}" class="w-full border rounded px-3 py-2"
                    {{ auth('admin')->user()->isSuperAdmin() ? '' : 'readonly' }}>
                @error('opening_time')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block mb-1 font-medium">Closing Time</label>
                <input type="time" name="closing_time" value="{{ old('closing_time', \Carbon\Carbon::parse($branch->closing_time)->format('H:i')) }}" class="w-full border rounded px-3 py-2"
                    {{ auth('admin')->user()->isSuperAdmin() ? '' : 'readonly' }}>
                @error('closing_time')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block mb-1 font-medium">Total Capacity</label>
                <input type="number" name="total_capacity" value="{{ old('total_capacity', $branch->total_capacity) }}" min="1" placeholder="e.g. 50" class="w-full border rounded px-3 py-2"
                    {{ auth('admin')->user()->isSuperAdmin() ? '' : 'readonly' }}>
                @error('total_capacity')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block mb-1 font-medium">Reservation Fee</label>
                <input type="number" step="0.01" name="reservation_fee" value="{{ old('reservation_fee', $branch->reservation_fee) }}" min="0" placeholder="e.g. 100.00" class="w-full border rounded px-3 py-2"
                    {{ auth('admin')->user()->isSuperAdmin() ? '' : 'readonly' }}>
                @error('reservation_fee')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div>
            <label class="block mb-1 font-medium">Cancellation Fee</label>
            <input type="number" step="0.01" name="cancellation_fee" value="{{ old('cancellation_fee', $branch->cancellation_fee) }}" min="0" placeholder="e.g. 50.00" class="w-full border rounded px-3 py-2"
                {{ auth('admin')->user()->isSuperAdmin() ? '' : 'readonly' }}>
            @error('cancellation_fee')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <label class="block mb-1 font-medium">Status</label>
            <select name="is_active" class="w-full border rounded px-3 py-2"
                {{ auth('admin')->user()->isSuperAdmin() ? '' : 'disabled' }}>
                <option value="1" {{ old('is_active', $branch->is_active) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !old('is_active', $branch->is_active) ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('is_active')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="flex gap-3 pt-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition"
                {{ (auth('admin')->user()->isSuperAdmin() || auth('admin')->user()->isOrganizationAdmin() || auth('admin')->user()->isBranchAdmin()) ? '' : 'disabled' }}>
                Update Branch
            </button>
            <a href="{{ route('admin.branches.index', ['organization' => $organization->id]) }}" class="text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection