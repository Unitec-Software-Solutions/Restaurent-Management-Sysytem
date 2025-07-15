@extends('layouts.admin')

@section('title', 'Edit Item Category')
@section('header-title', 'Edit Item Category - ' . $category->name)
@section('page-header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Edit Item Category</h1>
            <p class="text-gray-600">Update category information</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.item-categories.show', $category) }}"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-eye mr-2"></i>View Category
            </a>
            <a href="{{ route('admin.item-categories.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Categories
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6">
            <form action="{{ route('admin.item-categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')

                @if (Auth::guard('admin')->user()->is_super_admin)
                    <!-- Organization Info for Super Admin -->
                    <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Current Assignment</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Organization</label>
                                <p class="text-sm text-gray-900">{{ $category->organization->name ?? 'Not Assigned' }}</p>
                            </div>
                            <div>
                                <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Change Organization
                                </label>
                                <select name="organization_id" id="organization_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF9800] focus:border-transparent @error('organization_id') border-red-300 @enderror">
                                    <option value="">Keep Current</option>
                                    @foreach ($organizations as $org)
                                        <option value="{{ $org->id }}"
                                            {{ $category->organization_id == $org->id ? 'selected' : '' }}>
                                            {{ $org->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('organization_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Changing organization will move this category and affect inventory items using it.
                        </p>
                    </div>
                @else
                    <!-- Organization Info for Non-Super Admin -->
                    <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Organization</h3>
                        <p class="text-sm text-gray-900">{{ $category->organization->name ?? 'Not Assigned' }}</p>
                        <p class="text-xs text-gray-500 mt-1">Organization cannot be changed.</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Category Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF9800] focus:border-transparent @error('name') border-red-300 @enderror"
                            placeholder="Enter category name">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Code -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                            Category Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="code" id="code" value="{{ old('code', $category->code) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF9800] focus:border-transparent @error('code') border-red-300 @enderror"
                            placeholder="e.g., PROD, INGR, BEV" maxlength="10">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Maximum 10 characters. Must be unique within your
                            organization.</p>
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF9800] focus:border-transparent @error('description') border-red-300 @enderror"
                        placeholder="Brief description of this category...">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mt-6">
                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                            class="h-4 w-4 text-[#FF9800] focus:ring-[#FF9800] border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700">
                            Active Category
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Inactive categories cannot be used for new inventory items.</p>
                </div>

                <!-- Category Statistics -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Category Usage</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Items in this category:</span>
                            <span class="font-medium text-gray-900">{{ $category->items()->count() }} items</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Created:</span>
                            <span class="font-medium text-gray-900">{{ $category->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex gap-3 justify-end">
                    <a href="{{ route('admin.item-categories.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-[#FF9800] hover:bg-[#e68a00] text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Convert code to uppercase
            const codeInput = document.getElementById('code');
            codeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        });
    </script>
@endpush
