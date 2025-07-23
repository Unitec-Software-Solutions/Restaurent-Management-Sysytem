@extends('layouts.admin')

@section('title', 'Role Details')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-8 max-w-2xl mx-auto">
    <div class="mb-6 flex items-center gap-3">
        <i class="fas fa-id-badge text-indigo-500 text-3xl"></i>
        <h2 class="text-2xl font-bold text-gray-800">Role Details</h2>
    </div>
    <div class="mb-4">
        <span class="font-semibold text-gray-700">Role Name:</span>
        <span class="ml-2">{{ $role->name }}</span>
        @if($role->is_system_role)
            <span class="ml-2 px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 text-xs font-semibold">System</span>
        @endif
    </div>
    <div class="mb-4">
        <span class="font-semibold text-gray-700">Organization:</span>
        <span class="ml-2">{{ $role->organization->name ?? '-' }}</span>
    </div>
    <div class="mb-4">
        <span class="font-semibold text-gray-700">Branch:</span>
        <span class="ml-2">{{ $role->branch->name ?? 'Organization-wide' }}</span>
    </div>
    <div class="mb-4">
        <span class="font-semibold text-gray-700">Permissions:</span>
        @forelse($role->permissions as $permission)
            <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $permission->name }}</span>
        @empty
            <span class="text-gray-500">No permissions assigned</span>
        @endforelse
    </div>
    <div class="mt-6 flex gap-2">
        <a href="{{ route('admin.roles.edit', $role) }}" class="inline-flex items-center gap-1 bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-1 bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 transition">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>
</div>
@endsection
