@extends('layouts.admin')

@section('title', 'Assign Role')

@section('content')
<div class="bg-white rounded shadow p-6">
    <form action="{{ route('users.assign-role.store', $user) }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="role_id" class="block text-sm font-medium text-gray-700">Select Role</label>
            <select id="role_id" name="role_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">Select a role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ $user->roles->contains($role) ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Permissions</label>
            @php
                $oldPermissions = old('permissions', isset($user) ? $user->getAllPermissions()->pluck('id')->toArray() : []);
                $allPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')->get();
            @endphp
            @foreach($allPermissions as $permission)
                <div class="flex items-center">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                        {{ in_array($permission->id, $oldPermissions) ? 'checked' : '' }} class="mr-2">
                    <label class="text-sm text-gray-600">{{ $permission->name }}</label>
                </div>
            @endforeach
        </div>
        <div class="mt-6 flex justify-end">
            <a href="{{ route('users.index') }}" class="mr-3 bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">
                Cancel
            </a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                Assign Role
            </button>
        </div>
    </form>
</div>
@endsection
