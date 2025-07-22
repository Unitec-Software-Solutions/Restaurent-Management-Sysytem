{{-- filepath: resources/views/admin/roles/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold mb-2">Create New Role</h1>
            <p class="text-gray-600">Define a new role with specific permissions for your organization or branch</p>
        </div>
        <div>
            <a href="{{ route('admin.roles.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Back to Roles
            </a>
        </div>
    </div>

    <!-- Current Template Indicator -->
    <div id="selectedTemplateIndicator" class="mb-4">
        <span class="text-sm text-gray-600">Selected Template: </span>
        <span id="selectedTemplate" class="font-medium text-blue-600">None</span>
    </div>

    <!-- Predefined Role Templates -->
    @can('roles.templates')
    @if(!empty($availableTemplates))
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-blue-800 mb-3">
            <i class="fas fa-template mr-2"></i>Predefined Role Templates
        </h3>
        <p class="text-blue-700 mb-4">You can start with a predefined template and customize as needed:</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($availableTemplates as $templateName => $template)
                <div class="bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-{{ $template['color'] }}-300 hover:shadow-md transition-all"
                     onclick="loadRoleTemplate('{{ $templateName }}', {{ json_encode($template['permissions']) }})">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-{{ $template['icon'] }} text-{{ $template['color'] }}-500 mr-2"></i>
                        <h4 class="font-semibold text-gray-800">{{ $templateName }}</h4>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">{{ $template['description'] }}</p>
                    <div class="text-xs text-{{ $template['color'] }}-600">
                        {{ count($template['permissions']) }} permissions included
                    </div>
                </div>

            @endforeach
        </div>
    </div>
    @endif
    @endcan

    <div class="bg-white rounded shadow p-6">
        <form action="{{ route('admin.roles.store') }}" method="POST" id="roleForm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Role Basic Information -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Role Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           value="{{ old('name') }}"
                           placeholder="e.g., Custom Branch Manager">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Scope Selection -->
                @if(auth('admin')->user()->is_super_admin)
                    <div class="mb-4">
                        <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-1">Organization <span class="text-red-500">*</span></label>
                        <select name="organization_id" id="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Select Organization</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('organization_id'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->first('organization_id') }}</p>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch (Optional)</label>
                        <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Organization-wide role</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Leave empty for organization-wide role, or select a specific branch</p>
                    </div>
                @elseif(auth('admin')->user()->isOrganizationAdmin())
                    <div class="mb-4">
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                            <p class="text-sm text-purple-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                This role will be created for your organization: <strong>{{ auth('admin')->user()->organization->name }}</strong>
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch (Optional)</label>
                        <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Organization-wide role</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Leave empty for organization-wide role, or select a specific branch</p>
                    </div>
                @else
                    <div class="mb-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                This role will be created for your branch: <strong>{{ auth('admin')->user()->branch->name }}</strong>
                            </p>
                        </div>
                    </div>
                @endif

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($permissionDefinitions as $group => $definition)
                            @php
                                $groupPermissions = [];
                                if (isset($definition['permissions']) && is_array($definition['permissions'])) {
                                    foreach ($definition['permissions'] as $permKey => $permLabel) {
                                        if (isset($availablePermissions[$permKey])) {
                                            $groupPermissions[$permKey] = $permLabel;
                                        }
                                    }
                                }
                            @endphp
                            @if(!empty($groupPermissions))
                            <div class="border rounded p-4">
                                <h4 class="font-semibold mb-2">{{ ucfirst($group) }}</h4>
                                @foreach($groupPermissions as $permKey => $permLabel)
                                    <div class="flex items-center mb-2">
                                        <input type="checkbox" name="permissions[]"
                                            value="{{ $permKey }}"
                                            class="mr-2 rounded"
                                            id="perm_{{ $permKey }}"
                                            {{ in_array($permKey, old('permissions', [])) ? 'checked' : '' }}>
                                        <label for="perm_{{ $permKey }}">
                                            {{ $permLabel }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end space-x-3">
                <a href="{{ route('admin.roles.index') }}"
                   class="bg-gray-200 text-gray-800 py-2 px-4 rounded hover:bg-gray-300 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700 transition-colors">
                    Create Role
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Organization change handler
    const orgSelect = document.getElementById('organization_id');
    const branchSelect = document.getElementById('branch_id');

    if (orgSelect && branchSelect) {
        orgSelect.addEventListener('change', function() {
            const selectedOrgId = this.value;
            Array.from(branchSelect.options).forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                } else {
                    option.style.display = option.dataset.org === selectedOrgId ? 'block' : 'none';
                }
            });
            branchSelect.value = ''; // Reset branch selection
        });
    }

    // Handle group select all functionality
    document.querySelectorAll('.group-select-all').forEach(function(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const group = this.dataset.group;
            const groupPermissions = document.querySelectorAll('.' + group + '-permission');
            groupPermissions.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        // Check initial state
        const group = selectAllCheckbox.dataset.group;
        const groupPermissions = document.querySelectorAll('.' + group + '-permission');
        const allChecked = Array.from(groupPermissions).every(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
    });

    // Handle individual permission changes
    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const classes = Array.from(this.classList);
            const groupClass = classes.find(cls => cls.endsWith('-permission'));

            if (groupClass) {
                const group = groupClass.replace('-permission', '');
                const groupCheckboxes = document.querySelectorAll('.' + groupClass);
                const selectAllCheckbox = document.querySelector('[data-group="' + group + '"]');

                if (selectAllCheckbox) {
                    const allChecked = Array.from(groupCheckboxes).every(cb => cb.checked);
                    const noneChecked = Array.from(groupCheckboxes).every(cb => !cb.checked);

                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
                }
            }
        });
    });
});

// Load role template function
function loadRoleTemplate(templateName, permissions) {
    // Set role name and description
    document.getElementById('name').value = templateName;

    // Uncheck all permissions first
    document.querySelectorAll('input[name="permissions[]"]').forEach(function(checkbox) {
        checkbox.checked = false;
    });

    // Check permissions from template
    permissions.forEach(function(permission) {
        const checkbox = document.querySelector(`input[name="permissions[]"][value="${permission}"]`);
        if (checkbox) {
            checkbox.checked = true;
            // Trigger change event to update group checkboxes
            const event = new Event('change', { bubbles: true });
            checkbox.dispatchEvent(event);
        }
    });

    // Update group select all checkboxes
    document.querySelectorAll('.group-select-all').forEach(function(selectAllCheckbox) {
        const group = selectAllCheckbox.dataset.group;
        const groupCheckboxes = document.querySelectorAll('.' + group + '-permission');

        if (groupCheckboxes.length > 0) {
            const allChecked = Array.from(groupCheckboxes).every(cb => cb.checked);
            const noneChecked = Array.from(groupCheckboxes).every(cb => !cb.checked);

            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
        }
    });
}
</script>
@endsection


