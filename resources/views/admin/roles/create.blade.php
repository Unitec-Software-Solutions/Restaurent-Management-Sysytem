{{-- filepath: resources/views/admin/roles/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
<div class="bg-white rounded shadow p-6">
    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                <input type="text" id="name" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Scope (Optional)</label>
                <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Organization-wide</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mb-4">
            <label for="organization_id" class="block text-sm font-medium text-gray-700">Organization</label>
            <select name="organization_id" id="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach($organizations as $organization)
                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Module Permissions</label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($modules as $module)
                    <div class="mb-4 border rounded p-3">
                        <div class="font-semibold mb-2">{{ $module->name }}</div>
                        <div class="text-gray-500 mb-2">{{ $module->description }}</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach(is_array($module->permissions) ? $module->permissions : [] as $permission)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission }}"
                                        {{ (isset($role) && $role->permissions->pluck('name')->contains($permission)) ? 'checked' : '' }}>
                                    <span class="text-xs">{{ $permission }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <a href="{{ route('roles.index') }}" class="mr-3 bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">Cancel</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Create Role</button>
        </div>
    </form>
</div>
@endsection