{{-- filepath: resources/views/admin/users/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
<div class="bg-white rounded shadow p-6">
    @if($organizations->isEmpty())
        <div class="mb-4 bg-yellow-100 text-yellow-800 p-3 rounded flex items-center justify-between">
            <span>No organizations found. Please create an organization before adding users.</span>
            <a href="{{ route('admin.organizations.create') }}"
               class="ml-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Create Organization
            </a>
        </div>
    @elseif($branches->isEmpty())
        <div class="mb-4 bg-yellow-100 text-yellow-800 p-3 rounded">
            No branches found for your organization. Please create a branch before adding users.
        </div>
    @elseif($roles->isEmpty())
        <div class="mb-4 bg-yellow-100 text-yellow-800 p-3 rounded flex items-center justify-between">
            <span>No roles found. Please create a role before adding users.</span>
            <a href="{{ route('admin.roles.create') }}"
               class="ml-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Create Role
            </a>
        </div>
    @else
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" id="name" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                    <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="role_id" name="role_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            @if($role->name === 'Super Admin' && !auth('admin')->user()->isSuperAdmin())
                                @continue
                            @endif
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="block mb-1 font-medium">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" placeholder="Enter password"
                               class="w-full border rounded px-3 py-2 pr-10" required>
                        <button type="button" onclick="togglePassword('password')" class="absolute right-2 top-2 text-gray-500">
                            👁️
                        </button>
                    </div>
                    @error('password')
                        <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="block mb-1 font-medium">Confirm Password</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Re-enter password"
                               class="w-full border rounded px-3 py-2 pr-10" required>
                        <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-2 top-2 text-gray-500">
                            👁️
                        </button>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <a href="{{ route('admin.users.index') }}" class="mr-3 bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">Cancel</a>
                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Create User</button>
            </div>
        </form>
    @endif
</div>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endsection