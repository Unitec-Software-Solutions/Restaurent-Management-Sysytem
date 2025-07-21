
@extends('layouts.admin')

@section('content')
<div class="mx-auto px-4 py-8">
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
                        @if(isset($template['description']))
                        <div class="border rounded p-3 bg-gray-50">
                            <div class="font-semibold text-blue-600 mb-1">{{ $templateName }}</div>
                            <div class="text-sm text-gray-600 mb-2">{{ $template['description'] }}</div>
                            <button type="button" class="text-xs text-white bg-blue-500 hover:bg-blue-600 px-3 py-1 rounded" onclick="applyRoleTemplate('{{ $templateName }}')">Apply</button>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Permissions Section -->
            <div class="mt-8">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Permissions</label>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                        $oldPermissions = old('permissions', $role->permissions ? $role->permissions->pluck('name')->toArray() : []);
                        if ($oldPermissions === [null] || $oldPermissions === null) $oldPermissions = [];
                    @endphp
                    @foreach($permissionDefinitions as $category => $data)
                        @php
                            $categoryPermissions = [];
                            if (isset($data['permissions']) && is_array($data['permissions'])) {
                                foreach ($data['permissions'] as $permKey => $permLabel) {
                                    if (isset($availablePermissions[$permKey])) {
                                        $categoryPermissions[$permKey] = $permLabel;
                                    }
                                }
                            }
                            $categoryId = 'cat_' . preg_replace('/[^a-zA-Z0-9]/', '_', $category);
                        @endphp
                        @if(!empty($categoryPermissions))
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-800">{{ $data['label'] ?? ucfirst($category) }}</span>
                                <label class="flex items-center text-xs font-medium text-blue-600 cursor-pointer">
                                    <input type="checkbox" id="select_all_{{ $categoryId }}" class="mr-2 group-select-all" onclick="toggleCategoryPermissions('{{ $categoryId }}', this.checked)">
                                    Select All
                                </label>
                            </div>
                            <div class="space-y-1">
                                @foreach($categoryPermissions as $permissionKey => $permissionLabel)
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permissionKey }}"
                                               class="category-permission-{{ $categoryId }}"
                                               @if(in_array($permissionKey, $oldPermissions)) checked @endif>
                                        <span class="text-xs">{{ $permissionLabel }}</span>
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

function applyRoleTemplate(templateKey) {
    if (!roleTemplates[templateKey] || !roleTemplates[templateKey].permissions) return;
    document.querySelectorAll('input[type="checkbox"][name="permissions[]"]').forEach(function(cb) {
        cb.checked = false;
    });
    roleTemplates[templateKey].permissions.forEach(function(permission) {
        const checkbox = document.querySelector(`input[type="checkbox"][name="permissions[]"][value="${permission}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}

function toggleCategoryPermissions(categoryId, checked) {
    const checkboxes = document.querySelectorAll(`.category-permission-${categoryId}`);
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = checked;
    });
}
</script>
@endsection
