@extends('layouts.admin')

@section('title', isset($module) ? 'Edit Module' : 'Add Module')

@section('content')
<div class="bg-white rounded shadow p-6 max-w-lg mx-auto">
    <form action="{{ isset($module) ? route('admin.modules.update', $module) : route('admin.modules.store') }}" method="POST">
        @csrf
        @if(isset($module))
            @method('PUT')
        @endif

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" id="module-name"
                   value="{{ old('name', $module->name ?? '') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Slug</label>
            <input type="text" name="slug" id="module-slug"
                   value="{{ old('slug', $module->slug ?? '') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $module->description ?? '') }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
            <div id="permissions-container">
                @if(old('permissions', $module->permissions ?? []))
                    @foreach(old('permissions', $module->permissions ?? []) as $permission)
                        <div class="flex items-center mb-2 permission-item">
                            <input type="text" name="permissions[]" value="{{ $permission }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <button type="button" class="ml-2 text-red-600 remove-permission">×</button>
                        </div>
                    @endforeach
                @else
                    <div class="flex items-center mb-2 permission-item">
                        <input type="text" name="permissions[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <button type="button" class="ml-2 text-red-600 remove-permission">×</button>
                    </div>
                @endif
            </div>
            <button type="button" id="add-permission"
                    class="mt-2 bg-gray-200 text-gray-800 py-1 px-3 rounded text-sm">
                + Add Permission
            </button>
        </div>

        @if(isset($module))
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Active</label>
            <select name="is_active" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="1" {{ (old('is_active', $module->is_active ?? 1) == 1) ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ (old('is_active', $module->is_active ?? 1) == 0) ? 'selected' : '' }}>No</option>
            </select>
        </div>
        @endif

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.modules.index') }}" class="bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">Cancel</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                {{ isset($module) ? 'Update' : 'Create' }}
            </button>
        </div>
    </form>

    @if(isset($module))
    <div class="mt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Current Permissions</h3>
        <div class="flex flex-wrap gap-2">
            @foreach(is_array($module->permissions) ? $module->permissions : [] as $permission)
                <span>{{ $permission }}</span>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('module-name');
    const slugInput = document.getElementById('module-slug');
    let slugEdited = false;

    // If user edits slug manually, don't auto-update anymore
    slugInput.addEventListener('input', function() {
        slugEdited = true;
    });

    nameInput.addEventListener('input', function () {
        if (!slugEdited) {
            let slug = nameInput.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_') // replace non-alphanum with _
                .replace(/^_+|_+$/g, '');    // trim underscores
            slugInput.value = slug;
        }
    });
});
</script>
@endsection