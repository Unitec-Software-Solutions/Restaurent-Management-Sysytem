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
            @if(auth('admin')->user()->isSuperAdmin())
                <div>
                    <label for="organization_id" class="block text-sm font-medium text-gray-700">Organization</label>
                    <select name="organization_id" id="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Organization</option>
                        @foreach($organizations as $organization)
                            <option value="{{ $organization->id }}"
                                {{ old('organization_id', $role->organization_id ?? '') == $organization->id ? 'selected' : '' }}>
                                {{ $organization->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch (Optional)</label>
                    <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Organization-wide</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ old('branch_id', $role->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} ({{ $branch->organization->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Role Scope</label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center">
                            <input type="radio" name="scope" value="organization"
                                {{ old('scope', $role->scope ?? 'organization') == 'organization' ? 'checked' : '' }}>
                            <span class="ml-2">Organization-wide</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="scope" value="branch"
                                {{ old('scope', $role->scope ?? '') == 'branch' ? 'checked' : '' }}>
                            <span class="ml-2">Branch-specific</span>
                        </label>
                    </div>
                </div>
            @elseif(auth('admin')->user()->isAdmin())
                <input type="hidden" name="organization_id" value="{{ $organizations->first()->id }}">
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch (Optional)</label>
                    <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Organization-wide</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ old('branch_id', $role->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Role Scope</label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center">
                            <input type="radio" name="scope" value="organization"
                                {{ old('scope', $role->scope ?? 'organization') == 'organization' ? 'checked' : '' }}>
                            <span class="ml-2">Organization-wide</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="scope" value="branch"
                                {{ old('scope', $role->scope ?? '') == 'branch' ? 'checked' : '' }}>
                            <span class="ml-2">Branch-specific</span>
                        </label>
                    </div>
                </div>
            @else
                <input type="hidden" name="organization_id" value="{{ $organizations->first()->id }}">
                <input type="hidden" name="branch_id" value="{{ $branches->first()->id }}">
                <input type="hidden" name="scope" value="branch">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Branch</label>
                    <input type="text" value="{{ $branches->first()->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" readonly>
                </div>
            @endif
        </div>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Module Permissions</label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($modules as $module)
                    <div class="mb-4 border rounded p-3">
                        <div class="font-semibold mb-2">{{ $module->name }}</div>
                        <div class="text-gray-500 mb-2">{{ $module->description }}</div>
                        <div class="flex items-center mb-2">
                            <input type="checkbox"
                                id="select_all_{{ $module->id }}"
                                onclick="toggleModulePermissions('{{ $module->id }}', this.checked)">
                            <label for="select_all_{{ $module->id }}" class="ml-2 text-xs font-semibold text-blue-700 cursor-pointer">
                                Select All
                            </label>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach(is_array($module->permissions) ? $module->permissions : [] as $permission)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission }}"
                                        class="module-permission-{{ $module->id }}"
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
            <a href="{{ route('admin.roles.index') }}" class="mr-3 bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300">Cancel</a>
            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">Update Role</button>
        </div>
    </form>
</div>
<script>
function toggleModulePermissions(moduleId, checked) {
    document.querySelectorAll('.module-permission-' + moduleId).forEach(function(cb) {
        cb.checked = checked;
    });
}

// Attach listeners after DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // For each "Select All" checkbox
    document.querySelectorAll('[id^="select_all_"]').forEach(function(selectAllCheckbox) {
        const moduleId = selectAllCheckbox.id.replace('select_all_', '');
        // For each permission checkbox in this module
        document.querySelectorAll('.module-permission-' + moduleId).forEach(function(cb) {
            cb.addEventListener('change', function() {
                // If any permission is unchecked, uncheck "Select All"
                if (!this.checked) {
                    selectAllCheckbox.checked = false;
                } else {
                    // If all permissions are checked, check "Select All"
                    let allChecked = true;
                    document.querySelectorAll('.module-permission-' + moduleId).forEach(function(box) {
                        if (!box.checked) allChecked = false;
                    });
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });
        // On page load, set "Select All" if all permissions are checked
        let allChecked = true;
        const boxes = document.querySelectorAll('.module-permission-' + moduleId);
        boxes.forEach(function(box) {
            if (!box.checked) allChecked = false;
        });
        selectAllCheckbox.checked = boxes.length > 0 && allChecked;
    });
});
</script>
@endsection