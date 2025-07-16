@extends('layouts.admin')

@section('title', 'Modules Management')
@section('header-title', 'Modules Management')
@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Modules Management</h1>
            <p class="text-gray-600 mt-1">Manage system modules and their permissions</p>
        </div>
        <a href="{{ route('admin.modules.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition shadow-sm">
            <i class="fas fa-plus mr-2"></i> Add Module
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 text-green-700 bg-green-100 rounded p-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 text-red-700 bg-red-100 rounded p-3">{{ session('error') }}</div>
    @endif

    <!-- Filters with Export -->
    <x-module-filters
        :action="route('admin.modules.index')"
        :export-permission="'export_modules'"
        :export-filename="'modules_export.xlsx'">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </x-module-filters>

    <!-- Modules Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($modules as $module)
                <tr>
                    <td class="px-6 py-4">{{ $module->name }}</td>
                    <td class="px-6 py-4">{{ $module->slug }}</td>
                    <td class="px-6 py-4">{{ $module->description }}</td>
                    <td class="px-6 py-4">
                        @if($module->is_active)
                            <span class="text-green-600 font-semibold">Yes</span>
                        @else
                            <span class="text-red-600 font-semibold">No</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach(is_array($module->permissions) ? $module->permissions : [] as $permission)
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                    {{ $permission }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4 flex gap-2">
                        <a href="{{ route('admin.modules.edit', $module) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.modules.destroy', $module) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No modules found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
        </div>
    </div>
</div>
@endsection
