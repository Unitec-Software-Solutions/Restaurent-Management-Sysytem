@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="bg-white rounded shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-medium">All Users</h2>
        @can('create', App\Models\User::class)
            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                Create User
            </a>
        @endcan
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->branch->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->role)
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                    {{ $user->role->name }}
                                </span>
                            @else
                                <span class="text-yellow-600">No role assigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @can('update', $user)
                                <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                            @endcan
                            @can('assignRole', $user)
                                <a href="{{ route('users.assign-role', $user) }}" class="text-purple-600 hover:text-purple-900 mr-3">
                                    {{ $user->role ? 'Change Role' : 'Assign Role' }}
                                </a>
                            @endcan
                            @can('delete', $user)
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection