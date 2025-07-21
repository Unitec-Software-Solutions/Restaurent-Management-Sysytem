@extends('layouts.admin')

@section('title', 'Roles Management')

@section('content')

<div class="bg-white rounded-xl shadow-lg p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div class="flex items-center gap-3">
            <i class="fas fa-user-shield text-indigo-500 text-2xl"></i>
            <h2 class="text-2xl font-bold text-gray-800">Roles Management</h2>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 transition">
            <i class="fas fa-plus"></i> Add Role
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Branch</th>
                   <!-- Permissions column removed for cleaner UI -->
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($roles as $role)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900 flex items-center gap-2">
                            <i class="fas fa-id-badge text-indigo-400"></i>
                            {{ $role->name }}
                            @if($role->is_system_role ?? false)
                                <span class="ml-2 px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 text-xs font-semibold">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $role->organization->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $role->branch->name ?? 'Organization-wide' }}</td>
                       <!-- Permissions column removed for cleaner UI -->
                        <td class="px-6 py-4 flex gap-2">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="inline-flex items-center gap-1 bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('admin.roles.show', $role) }}" class="inline-flex items-center gap-1 bg-indigo-500 text-white px-3 py-1 rounded hover:bg-indigo-600 transition">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                       <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                            <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                            <div class="text-lg font-semibold">No roles found</div>
                            <div class="text-sm mt-1 mb-3">Create a role to get started with user management and permissions.</div>
                            <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                                <i class="fas fa-plus"></i> Create Role
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
