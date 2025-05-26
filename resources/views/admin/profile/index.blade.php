@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white p-6 rounded-lg shadow-md space-y-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Admin Profile Details</h2>

        <!-- Admin Info -->
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Personal Info</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600">Name</label>
                    <input type="text" value="{{ $admin->name }}" readonly class="w-full border bg-gray-100 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Email</label>
                    <input type="text" value="{{ $admin->email }}" readonly class="w-full border bg-gray-100 rounded px-3 py-2">
                </div>
            </div>
        </div>

        <!-- Branch Info -->
        @if ($admin->branch)
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Branch Info</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600">Branch Name</label>
                    <input type="text" value="#" readonly class="w-full border bg-gray-100 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Branch Address</label>
                    <input type="text" value="{{ $admin->branch->address }}" readonly class="w-full border bg-gray-100 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Branch Phone</label>
                    <input type="text" value="{{ $admin->branch->phone }}" readonly class="w-full border bg-gray-100 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Branch Email</label>
                    <input type="text" value="{{ $admin->branch->email }}" readonly class="w-full border bg-gray-100 rounded px-3 py-2">
                </div>
            </div>
        </div>
        @endif

        <!-- Organization Info -->
        @if ($admin->organization)
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Organization Info</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600">Organization Name</label>
                    <input type="text" value="{{ $admin->organization->name }}" readonly class="w-full border bg-gray-100 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Organization Email</label>
                    <input type="text" value="{{ $admin->organization->email }}" readonly class="w-full border bg-gray-100 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Phone</label>
                    <input type="text" value="{{ $admin->organization->phone }}" readonly class="w-full border bg-gray-100 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Website</label>
                    <input type="text" value="{{ $admin->organization->website }}" readonly class="w-full border bg-gray-100 rounded px-3 py-2">
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
