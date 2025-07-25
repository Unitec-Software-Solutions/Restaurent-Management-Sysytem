
@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    @can('roles.templates')
        @if(!empty($availableTemplates))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">
                <i class="fas fa-template mr-2"></i>Role Templates
            </h3>
            <p class="text-blue-700 mb-4">Apply a template to update role permissions:</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($availableTemplates as $templateKey => $template)
                    <div class="bg-white rounded-lg border border-blue-200 p-4 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-gray-800 mb-2">{{ $templateKey }}</h4>
                        <p class="text-sm text-gray-600 mb-3">{{ $template['description'] ?? 'No description available' }}</p>
                        <button type="button"
                                onclick="applyRoleTemplate('{{ $templateKey }}')"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="fas fa-check-circle mr-1"></i>Apply Template
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    @endcan

    <div class="bg-white rounded shadow p-6">
        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(auth('admin')->user()->isSuperAdmin())
                <div>
                    <label for="organization_id" class="block text-sm font-medium text-gray-700">Organization</label>
                    <select name="organization_id" id="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Organization</option>
                        @foreach($organizations as $organization)
                            <option value="{{ $organization->id }}" {{ old('organization_id', $role->organization_id) == $organization->id ? 'selected' : '' }}>
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
                            <option value="{{ $branch->id }}" {{ old('branch_id', $role->branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <!-- Role Templates Section -->
            @if(!empty($availableTemplates))
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Quick Role Templates</label>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    @foreach($availableTemplates as $templateName => $template)
                        <!-- Template display logic here if needed -->
                    @endforeach
                </div>
            </div>
        @endif

            <!-- Permissions Section -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-key mr-2"></i>Role Permissions
                </h3>
                <p class="text-gray-600 mb-4">Select the permissions that this role should have access to:</p>

                <!-- Search and Bulk Actions -->
                <div class="flex items-center justify-between mb-4">
                    <div class="relative flex-grow mr-4">
                        <input type="text" id="permissionSearch"
                               class="w-full pl-10 pr-4 py-2 border rounded-lg"
                               placeholder="Search permissions..."
                               onkeyup="filterPermissions(this.value)">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button type="button" onclick="selectAllPermissions()"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="fas fa-check-square mr-1"></i>Select All
                        </button>
                        <button type="button" onclick="deselectAllPermissions()"
                                class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                            <i class="fas fa-square mr-1"></i>Deselect All
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($permissionDefinitions as $categoryKey => $category)
                        @php
                            // Filter permissions to only show those available to this admin
                            $categoryPermissions = [];
                            if (isset($category['permissions']) && is_array($category['permissions'])) {
                                foreach ($category['permissions'] as $permKey => $permLabel) {
                                    if (isset($availablePermissions[$permKey])) {
                                        $categoryPermissions[$permKey] = $permLabel;
                                    }
                                }
                            }
                        @endphp

                        @if(!empty($categoryPermissions))
                        <div class="border border-gray-200 rounded-lg p-4 permission-category" data-category="{{ $categoryKey }}">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-{{ $category['icon'] }} mr-2 text-blue-500"></i>
                                    {{ $category['name'] }}
                                </h4>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           class="group-select-all rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                           data-group="{{ $categoryKey }}">
                                    <span class="ml-2 text-xs text-indigo-600 font-medium">All</span>
                                </label>
                            </div>

                            <p class="text-xs text-gray-500 mb-3">{{ $category['description'] }}</p>

                            <div class="space-y-2">
                                @foreach($categoryPermissions as $permissionKey => $description)
                                    <label class="flex items-start cursor-pointer group">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permissionKey }}"
                                                   class="permission-checkbox {{ $categoryKey }}-permission rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                   {{ in_array($permissionKey, $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}>
                                        </div>
                                        <div class="ml-2">
                                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $description }}</span>
                                            @if(isset($category['tooltips'][$permissionKey]))
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $category['tooltips'][$permissionKey] }}</p>
                                            @endif
                                        </div>
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
</div>
<script>
// Role template data
const roleTemplates = @json($availableTemplates);

// Filter permissions based on search input
function filterPermissions(searchTerm) {
    const categories = document.querySelectorAll('.permission-category');
    searchTerm = searchTerm.toLowerCase();

    categories.forEach(category => {
        let hasMatch = false;
        const permissions = category.querySelectorAll('.permission-checkbox');

        permissions.forEach(permission => {
            const label = permission.closest('label');
            const text = label.textContent.toLowerCase();
            const matches = text.includes(searchTerm);
            label.style.display = matches ? 'flex' : 'none';
            if (matches) hasMatch = true;
        });

        category.style.display = hasMatch ? 'block' : 'none';
    });
}

// Select all permissions
function selectAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.checked = true;
        triggerChangeEvent(checkbox);
    });
}

// Deselect all permissions
function deselectAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        triggerChangeEvent(checkbox);
    });
}

// Trigger change event
function triggerChangeEvent(element) {
    const event = new Event('change', { bubbles: true });
    element.dispatchEvent(event);
}

// Apply role template
function applyRoleTemplate(templateKey) {
    if (!roleTemplates[templateKey] || !roleTemplates[templateKey].permissions) return;

    // Deselect all permissions first
    document.querySelectorAll('input[name="permissions[]"]').forEach(function(checkbox) {
        checkbox.checked = false;
    });

    // Select template permissions
    roleTemplates[templateKey].permissions.forEach(function(permission) {
        const checkbox = document.querySelector(`input[name="permissions[]"][value="${permission}"]`);
        if (checkbox) {
            checkbox.checked = true;
            // Trigger change event to update group checkboxes
            triggerChangeEvent(checkbox);
        }
    });

    // Update group checkboxes
    updateGroupCheckboxes();
}

// Initialize group select all functionality
function initializeGroupSelectors() {
    document.querySelectorAll('.group-select-all').forEach(function(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const group = this.dataset.group;
            const groupPermissions = document.querySelectorAll('.' + group + '-permission');
            groupPermissions.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
                triggerChangeEvent(checkbox);
            });
        });

        // Check initial state
        const group = selectAllCheckbox.dataset.group;
        const groupPermissions = document.querySelectorAll('.' + group + '-permission');
        const allChecked = Array.from(groupPermissions).every(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
    });
}

// Update group checkboxes state
function updateGroupCheckboxes() {
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

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeGroupSelectors();

    // Handle individual permission changes
    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const classes = Array.from(this.classList);
            const groupClass = classes.find(cls => cls.endsWith('-permission'));

            if (groupClass) {
                updateGroupCheckboxes();
            }
        });
    });
});
</script>
@endsection
