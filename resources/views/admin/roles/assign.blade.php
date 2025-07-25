@extends('layouts.admin')

@section('content')
<div class="bg-white rounded shadow p-6 max-w-md mx-auto">
    <form action="{{ route('roles.assign', (isset($user) && isset($user->id)) ? $user->id : '') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="role_id" class="block text-sm font-medium text-gray-700">Select Role</label>
            <select name="role_id" id="role_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
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
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Assign Role</button>
        </div>
    </form>
</div>
@endsection
