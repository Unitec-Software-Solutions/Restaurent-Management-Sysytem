@extends('layouts.admin')

@section('title', 'Roles Management')

@section('content')
<div class="bg-white rounded shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold">Roles</h2>
        <a href="{{ route('admin.roles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Add Role
        </a>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $role)
                <tr>
                    <td class="px-6 py-4">{{ $role->name }}</td>
                    <td class="px-6 py-4">{{ $role->organization->name ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $role->branch->name ?? 'Organization-wide' }}</td>
                    <td class="px-6 py-4 flex gap-2">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</a>          
                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No roles found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection