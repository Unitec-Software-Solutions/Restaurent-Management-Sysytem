{{-- filepath: resources/views/admin/users/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
    <div class="bg-white rounded shadow p-6 max-w-xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">User Details</h2>
        <div class="mb-4"><strong>Name:</strong> <span>{{ $user->name }}</span></div>
        <div class="mb-4"><strong>Email:</strong> <span>{{ $user->email }}</span></div>
        <div class="mb-4"><strong>Phone Number:</strong> <span>{{ $user->phone_number ?? '-' }}</span></div>
        <div class="mb-4"><strong>Organization:</strong> <span>{{ $user->organization->name ?? '-' }}</span></div>
        <div class="mb-4"><strong>Branch:</strong> <span>{{ $user->branch->name ?? '-' }}</span></div>
        <div class="mb-4"><strong>Role:</strong> <span>{{ $user->userRole->name ?? '-' }}</span></div>
        <div class="mb-4"><strong>Status:</strong>
            <span class="{{ $user->is_active ? 'text-green-600' : 'text-red-600' }}">
                {{ $user->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="mb-4">
            <strong>Created By:</strong>
            <span>
                @if($user->creator)
                    {{ $user->creator->name }}
                    @if(isset($user->creator) && $user->creator->isSuperAdmin())
                        (Super Admin)
                    @endif
                @else
                    -
                @endif
            </span>
        </div>
        <div class="mb-4"><strong>Created At:</strong> <span>{{ $user->created_at ? $user->created_at->format('Y-m-d H:i') : '-' }}</span></div>
        <div class="mb-4"><strong>Updated At:</strong> <span>{{ $user->updated_at ? $user->updated_at->format('Y-m-d H:i') : '-' }}</span></div>
    </div>
    <div class="mt-6 flex justify-end">
        <a href="{{ route('admin.users.index') }}" class="bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">Back to Users</a>
        @can('update', $user)
            <a href="{{ route('admin.users.edit', $user) }}" class="ml-3 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Edit</a>
        @endcan
    </div>
@endsection