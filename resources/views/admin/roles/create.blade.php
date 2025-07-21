{{-- filepath: resources/views/admin/roles/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
<div class="mx-auto px-4 py-8">
    <!-- Header Section -->
     <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
         <div class="flex justify-between items-center">
            <div>
                <div class="flex items-center gap-2">
                    {{-- <i class="fas fa-user-shield text-2xl text-indigo-500"></i> --}}
                    <h1 class="text-2xl font-bold text-gray-900">Create New Role</h1>
                </div>
                <p class="text-gray-600 mt-1">Define a new role with specific permissions for your organization or branch</p>
            </div>

        </div>
    </div>

    <!-- Predefined Role Templates -->
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

    <div class="bg-white rounded-lg shadow-sm p-6">
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
            </div>

            <!-- Permission Categories -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-key mr-2"></i>Role Permissions
                </h3>
                <p class="text-gray-600 mb-4">Select the permissions that this role should have access to:</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($permissionDefinitions as $categoryKey => $category)
                        @php
                            // Filter permissions to only show those available to this admin
                            $categoryPermissions = array_intersect_key(
                                $category['permissions'],
                                $availablePermissions
                            );
                        @endphp

                        @if(!empty($categoryPermissions))
                        <div class="border border-gray-200 rounded-lg p-4">
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
                                @foreach($categoryPermissions as $permission => $description)
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission }}"
                                               class="permission-checkbox {{ $categoryKey }}-permission rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                               {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">
                                            {{ $description }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
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
    // Handle group select all functionality
    document.querySelectorAll('.group-select-all').forEach(function(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const group = this.dataset.group;
            const groupPermissions = document.querySelectorAll('.' + group + '-permission');

            groupPermissions.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
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
        const checkbox = document.querySelector('input[value="' + permission + '"]');
        if (checkbox) {
            checkbox.checked = true;
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

                {{-- <!-- Scope Selection -->
                @if(auth('admin')->user()->isSuperAdmin())
                    <div class="mb-4">
                        <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                        <select name="organization_id" id="organization_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Select Organization</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch (Optional)</label>
                        <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Organization-wide role</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} ({{ $branch->organization->name }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Leave empty for organization-wide role</p>
                    </div>
                @elseif(auth('admin')->user()->organization_id && !auth('admin')->user()->branch_id)
                    <!-- Organization Admin -->
                    <input type="hidden" name="organization_id" value="{{ auth('admin')->user()->organization_id }}">
                    <div class="mb-4">
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch (Optional)</label>
                        <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Organization-wide role</option>
                            @if(isset($branches))
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Leave empty for organization-wide role or select specific branch</p>
                    </div>
                @elseif(auth('admin')->user()->branch_id)
                    <!-- Branch Admin -->
                    <input type="hidden" name="organization_id" value="{{ auth('admin')->user()->organization_id }}">
                    <input type="hidden" name="branch_id" value="{{ auth('admin')->user()->branch_id }}">
                    <div class="mb-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                This role will be created for your branch: <strong>{{ auth('admin')->user()->branch->name }}</strong>
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Permission Categories -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-key mr-2"></i>Role Permissions
                </h3>
                <p class="text-gray-600 mb-4">Select the permissions that this role should have access to:</p>

                @php
                    $permissionGroups = [
                        'Organization Management' => ['organizations.view', 'organizations.create', 'organizations.edit', 'organizations.delete', 'organizations.activate', 'organizations.manage'],
                        'Branch Management' => ['branches.view', 'branches.create', 'branches.edit', 'branches.delete', 'branches.activate', 'branches.manage'],
                        'User Management' => ['users.view', 'users.create', 'users.edit', 'users.delete', 'users.activate', 'users.manage', 'users.impersonate'],
                        'Roles & Permissions' => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'roles.assign', 'roles.manage'],
                        'Menu Management' => ['menus.view', 'menus.create', 'menus.edit', 'menus.delete', 'menus.activate', 'menus.manage'],
                        'Order Management' => ['orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.process', 'orders.refund', 'orders.manage'],
                        'Inventory Management' => ['inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete', 'inventory.adjust', 'inventory.manage'],
                        'Supplier Management' => ['suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete', 'suppliers.manage'],
                        'Reservation Management' => ['reservations.view', 'reservations.create', 'reservations.edit', 'reservations.delete', 'reservations.confirm', 'reservations.manage'],
                        'Kitchen Operations' => ['kitchen.view', 'kitchen.manage', 'kitchen.stations', 'kitchen.production'],
                        'Reports & Analytics' => ['reports.view', 'reports.advanced', 'reports.export', 'reports.manage'],
                        'Staff Management' => ['staff.view', 'staff.create', 'staff.edit', 'staff.delete', 'staff.schedule', 'staff.manage'],
                        'Module Management' => ['modules.view', 'modules.create', 'modules.edit', 'modules.delete', 'modules.activate', 'modules.manage'],
                        'Subscription Management' => ['subscription.view', 'subscription.edit', 'subscription.billing', 'subscription.manage'],
                        'System Settings' => ['settings.view', 'settings.edit', 'settings.backup', 'settings.maintenance', 'settings.manage'],
                    ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($permissionGroups as $groupName => $permissions)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-800">{{ $groupName }}</h4>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           class="group-select-all rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                           data-group="{{ str_replace(' ', '_', strtolower($groupName)) }}">
                                    <span class="ml-2 text-xs text-indigo-600 font-medium">All</span>
                                </label>
                            </div>

                            <div class="space-y-2">
                                @foreach($permissions as $permission)
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission }}"
                                               class="permission-checkbox {{ str_replace(' ', '_', strtolower($groupName)) }}-permission rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                               {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">
                                            {{ ucwords(str_replace(['.', '_'], ' ', explode('.', $permission)[1])) }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
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
    // Handle group select all functionality
    document.querySelectorAll('.group-select-all').forEach(function(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const group = this.dataset.group;
            const groupPermissions = document.querySelectorAll('.' + group + '-permission');

            groupPermissions.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
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
    // Set role name
    document.getElementById('name').value = templateName;

    // Uncheck all permissions first
    document.querySelectorAll('input[name="permissions[]"]').forEach(function(checkbox) {
        checkbox.checked = false;
    });

    // Check permissions from template
    permissions.forEach(function(permission) {
        const checkbox = document.querySelector('input[value="' + permission + '"]');
        if (checkbox) {
            checkbox.checked = true;
        }
    });

    // Update group select all checkboxes
    document.querySelectorAll('.group-select-all').forEach(function(selectAllCheckbox) {
        const group = selectAllCheckbox.dataset.group;
        const groupCheckboxes = document.querySelectorAll('.' + group + '-permission');
        const allChecked = Array.from(groupCheckboxes).every(cb => cb.checked);
        const noneChecked = Array.from(groupCheckboxes).every(cb => !cb.checked);

        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
    });

    // Show success message
    const message = document.createElement('div');
    message.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
    message.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Template "' + templateName + '" loaded successfully!';
    document.body.appendChild(message);

    setTimeout(function() {
        message.remove();
    }, 3000);
}
</script>
@endsection --}}
