@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="bg-white rounded shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold">Users</h2>
        <a href="{{ route('admin.users.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Create User
        </a>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-4 py-2">#</th>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Role</th>
                <th class="px-4 py-2">Organization</th>
                <th class="px-4 py-2">Branch</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
                <tr>
                    <td class="px-4 py-2">{{ $users->firstItem() + $index }}</td>
                    <td class="px-4 py-2">{{ $user->name }}</td>
                    <td class="px-4 py-2">{{ $user->email }}</td>
                    <td class="px-4 py-2">{{ $user->userRole->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $user->organization->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $user->branch->name ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-2 flex space-x-1">
                        <a href="{{ route('admin.users.show', $user) }}" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">View</a>
                        @can('update', $user)
                            <a href="{{ route('admin.users.edit', $user) }}" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</a>
                        @endcan
                        @can('delete', $user)
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Delete</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-2 text-center text-gray-500">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection