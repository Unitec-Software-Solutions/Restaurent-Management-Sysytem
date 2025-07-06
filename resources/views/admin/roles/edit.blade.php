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
        <!-- Role Templates Section -->
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Role Templates</label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                @if(isset($availableTemplates) && !empty($availableTemplates))
                    @foreach($availableTemplates as $templateName => $template)
                        @if(isset($template['description']))
                            <div class="border rounded p-3 bg-gray-50">
                                <div class="font-semibold text-blue-600 mb-1">{{ $templateName }}</div>
                                <div class="text-sm text-gray-600 mb-2">{{ $template['description'] }}</div>
                                <button type="button" 
                                        class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200"
                                        onclick="applyRoleTemplate('{{ $templateName }}')">
                                    Apply Template
                                </button>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="col-span-3 text-center text-gray-500">
                        <p>No role templates available for your current access level.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Permissions Section -->
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($permissionDefinitions as $category => $data)
                    @if(isset($availablePermissions[$category]) && !empty($availablePermissions[$category]))
                        <div class="mb-4 border rounded p-3">
                            <div class="font-semibold mb-2 text-gray-800">{{ $data['label'] }}</div>
                            <div class="text-gray-500 text-sm mb-2">{{ $data['description'] }}</div>
                            <div class="flex items-center mb-2">
                                <input type="checkbox"
                                    id="select_all_{{ $category }}"
                                    onclick="toggleCategoryPermissions('{{ $category }}', this.checked)">
                                <label for="select_all_{{ $category }}" class="ml-2 text-xs font-semibold text-blue-700 cursor-pointer">
                                    Select All
                                </label>
                            </div>
                            <div class="space-y-1">
                                @foreach($availablePermissions[$category] as $permission)
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission }}"
                                            class="category-permission-{{ $category }}"
                                            {{ $role->permissions->pluck('name')->contains($permission) ? 'checked' : '' }}>
                                        <span class="text-xs">{{ str_replace(['_', '-'], ' ', ucwords($permission, '_-')) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
// Role template data
const roleTemplates = @json($availableTemplates);

function applyRoleTemplate(templateKey) {
    if (!roleTemplates[templateKey] || !roleTemplates[templateKey].permissions) return;
    
    // First, uncheck all permission checkboxes
    document.querySelectorAll('input[name="permissions[]"]').forEach(function(cb) {
        cb.checked = false;
    });
    
    // Then check the permissions for this template
    roleTemplates[templateKey].permissions.forEach(function(permission) {
        const checkbox = document.querySelector(`input[name="permissions[]"][value="${permission}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
    
    // Update "Select All" checkboxes
    updateSelectAllCheckboxes();
}

function toggleCategoryPermissions(category, checked) {
    document.querySelectorAll('.category-permission-' + category).forEach(function(cb) {
        cb.checked = checked;
    });
}

function updateSelectAllCheckboxes() {
    document.querySelectorAll('[id^="select_all_"]').forEach(function(selectAllCheckbox) {
        const category = selectAllCheckbox.id.replace('select_all_', '');
        const categoryCheckboxes = document.querySelectorAll('.category-permission-' + category);
        const checkedCount = document.querySelectorAll('.category-permission-' + category + ':checked').length;
        selectAllCheckbox.checked = categoryCheckboxes.length > 0 && checkedCount === categoryCheckboxes.length;
    });
}

// Attach listeners after DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // For each "Select All" checkbox
    document.querySelectorAll('[id^="select_all_"]').forEach(function(selectAllCheckbox) {
        const category = selectAllCheckbox.id.replace('select_all_', '');
        // For each permission checkbox in this category
        document.querySelectorAll('.category-permission-' + category).forEach(function(cb) {
            cb.addEventListener('change', function() {
                updateSelectAllCheckboxes();
            });
        });
    });
    
    // Initial state of "Select All" checkboxes
    updateSelectAllCheckboxes();
});
</script>
@endsection