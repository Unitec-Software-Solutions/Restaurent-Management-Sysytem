{{-- filepath: resources/views/admin/users/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
    <div class="bg-white rounded shadow p-6 max-w-xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Admin Details</h2>
        <div class="mb-4"><strong>Name:</strong> <span>{{ $admin->name }}</span></div>
        <div class="mb-4"><strong>Email:</strong> <span>{{ $admin->email }}</span></div>
        <div class="mb-4"><strong>Phone Number:</strong> <span>{{ $admin->phone_number ?? '-' }}</span></div>
        <div class="mb-4"><strong>Organization:</strong> <span>{{ $admin->organization->name ?? '-' }}</span></div>
        <div class="mb-4"><strong>Branch:</strong> <span>{{ $admin->branch->name ?? '-' }}</span></div>
        <div class="mb-4"><strong>Role(s):</strong>
            @if($admin->roles && $admin->roles->count() > 0)
                <div class="flex flex-wrap gap-1">
                    @foreach($admin->roles as $role)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                            {{ $role->name }}
                        </span>
                    @endforeach
                </div>
            @else
                <span class="text-gray-400 text-sm">No roles</span>
            @endif
        </div>
        <div class="mb-4"><strong>Status:</strong>
            <span class="{{ $admin->is_active ? 'text-green-600' : 'text-red-600' }}">
                {{ $admin->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="mb-4">
            <strong>Created By:</strong>
            <span>
                @if($admin->creator)
                    {{ $admin->creator->name }}
                    @if(isset($admin->creator) && $admin->creator->isSuperAdmin())
                        (Super Admin)
                    @endif
                @else
                    -
                @endif
            </span>
        </div>
        <div class="mb-4"><strong>Created At:</strong> <span>{{ $admin->created_at ? $admin->created_at->format('Y-m-d H:i') : '-' }}</span></div>
        <div class="mb-4"><strong>Updated At:</strong> <span>{{ $admin->updated_at ? $admin->updated_at->format('Y-m-d H:i') : '-' }}</span></div>
    </div>
    <div class="mt-6 flex justify-end">
        <a href="{{ route('admin.users.index') }}" class="bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">Back to Users</a>
        @can('update', $user)
            <a href="{{ route('admin.users.edit', $user) }}" class="ml-3 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Edit</a>
        @endcan
    </div>
@endsection
