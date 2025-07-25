@extends('layouts.admin')

@section('title', 'Create Menu Category')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Menu Category</h1>
                <p class="text-gray-600">Add a new category to organize your menu items</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.menu-categories.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Categories
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm">
        <form method="POST" action="{{ route('admin.menu-categories.store') }}" class="p-6">
            @csrf

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Category Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-300 @enderror"
                           placeholder="e.g., Appetizers, Main Courses, Desserts">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                        Display Order
                    </label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 1) }}" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sort_order') border-red-300 @enderror"
                           placeholder="1">
                    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first in the menu</p>
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Branch and Organization Selection -->
            @if(auth('admin')->user()->is_super_admin)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Organization -->
                    <div>
                        <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Organization <span class="text-red-500">*</span>
                        </label>
                        <select id="organization_id" name="organization_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('organization_id') border-red-300 @enderror">
                            <option value="">Select Organization</option>
                            @foreach($organizations as $organization)
                                <option value="{{ $organization->id }}" {{ old('organization_id') == $organization->id ? 'selected' : '' }}>
                                    {{ $organization->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('organization_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Branch -->
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Branch <span class="text-red-500">*</span>
                        </label>
                        <select id="branch_id" name="branch_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('branch_id') border-red-300 @enderror">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @elseif(optional(auth('admin')->user())->getAttribute('organization_id') && !optional(auth('admin')->user())->getAttribute('branch_id'))
                <!-- Organization admin can select branch -->
                <div class="mb-6">
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Branch <span class="text-red-500">*</span>
                    </label>
                    <select id="branch_id" name="branch_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('branch_id') border-red-300 @enderror">
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-300 @enderror"
                          placeholder="Brief description of this category...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Image URL -->
            <div class="mb-6">
                <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">
                    Category Image URL
                </label>
                <input type="url" id="image_url" name="image_url" value="{{ old('image_url') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('image_url') border-red-300 @enderror"
                       placeholder="https://example.com/image.jpg">
                <p class="mt-1 text-xs text-gray-500">Optional: URL to an image representing this category</p>
                @error('image_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Options -->
            <div class="mb-6">
                <div class="flex flex-wrap gap-6">
                    <!-- Is Active -->
                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">
                            Active Category
                        </label>
                    </div>

                    <!-- Is Featured -->
                    <div class="flex items-center">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                               {{ old('is_featured') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_featured" class="ml-2 text-sm text-gray-700">
                            Featured Category
                        </label>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Featured categories may be highlighted in menus</p>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.menu-categories.index') }}" 
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i> Create Category
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const organizationSelect = document.getElementById('organization_id');
    const branchSelect = document.getElementById('branch_id');
    
    if (organizationSelect && branchSelect) {
        organizationSelect.addEventListener('change', function() {
            const organizationId = this.value;
            
            // Clear branch options
            branchSelect.innerHTML = '<option value="">Loading branches...</option>';
            branchSelect.disabled = true;
            
            if (organizationId) {
                // Fetch branches for selected organization
                fetch(`/admin/api/menu-categories/organizations/${organizationId}/branches`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        branchSelect.innerHTML = '<option value="">Select Branch</option>';
                        
                        if (data.success && data.branches) {
                            data.branches.forEach(branch => {
                                const option = document.createElement('option');
                                option.value = branch.id;
                                option.textContent = branch.name;
                                branchSelect.appendChild(option);
                            });
                        }
                        
                        branchSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching branches:', error);
                        branchSelect.innerHTML = '<option value="">Error loading branches</option>';
                        branchSelect.disabled = false;
                    });
            } else {
                branchSelect.innerHTML = '<option value="">Select Branch</option>';
                branchSelect.disabled = false;
            }
        });
    }
});
</script>
@endpush
@endsection
