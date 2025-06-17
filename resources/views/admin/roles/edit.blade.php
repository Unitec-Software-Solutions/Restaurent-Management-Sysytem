{{-- filepath: resources/views/admin/roles/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
<div class="bg-white rounded shadow p-6">
    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch (Optional)</label>
                <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Organization-wide</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $role->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Module Permissions</label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($modules as $module)
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="module_{{ $module->id }}" name="modules[]" type="checkbox" value="{{ $module->id }}"
                                {{ $role->modules && $role->modules->pluck('id')->contains($module->id) ? 'checked' : '' }}
                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="module_{{ $module->id }}" class="font-medium text-gray-700">
                                {{ $module->name }}
                            </label>
                            <p class="text-gray-500">{{ $module->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <a href="{{ route('admin.roles.index') }}" class="mr-3 bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">Cancel</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Update Role</button>
        </div>
    </form>
</div>
@endsection