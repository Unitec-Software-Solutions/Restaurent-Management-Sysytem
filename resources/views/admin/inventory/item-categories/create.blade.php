@extends('layouts.admin')

@section('title', 'Create Item Category')
@section('header-title', 'Create Item Category')
@section('content')
    <div class="p-4 rounded-lg">
        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Card Header -->
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Create New Item Category</h2>
                    <p class="text-sm text-gray-500">Add a new inventory item category</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.item-categories.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Item Categories
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <form action="{{ route('admin.item-categories.store') }}" method="POST">
                        @csrf

                        @if (Auth::guard('admin')->user()->is_super_admin)
                            <!-- Organization Selection for Super Admin -->
                            <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-1">
                                            Target Organization <span class="text-red-500">*</span>
                                        </label>
                                        <select name="organization_id" id="organization_id" required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('organization_id') border-red-300 @enderror">
                                            <option value="">Select Organization</option>
                                            @foreach ($organizations as $org)
                                                <option value="{{ $org->id }}"
                                                    {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                                    {{ $org->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('organization_id')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mt-3 text-sm text-indigo-700">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Category will be created for the selected organization
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Category Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-300 @enderror"
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
                                <input type="text" name="code" id="code" value="{{ old('code') }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('code') border-red-300 @enderror"
                                    placeholder="e.g., PROD, INGR, BEV" maxlength="10">
                                @error('code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Maximum 10 characters. Will be used as a unique
                                    identifier.
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-300 @enderror"
                                placeholder="Brief description of this category...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mt-6">
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}
                                    class="h-5 w-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all duration-150 checked:bg-indigo-600 checked:border-indigo-600">
                                <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700 select-none">
                                    Active Category
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Active categories can be used when creating inventory
                                items.</p>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex gap-3 justify-end">
                            <a href="{{ route('admin.item-categories.index') }}"
                                class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-indigo-50 text-indigo-700 transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                                <i class="fas fa-save mr-2"></i>Create Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endsection

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Auto-generate code from name
                    const nameInput = document.getElementById('name');
                    const codeInput = document.getElementById('code');

                    nameInput.addEventListener('input', function() {
                        if (!codeInput.value) {
                            // Generate code from name (first 3-4 letters, uppercase)
                            const code = this.value
                                .replace(/[^a-zA-Z0-9]/g, '')
                                .substring(0, 4)
                                .toUpperCase();
                            codeInput.value = code;
                        }
                    });

                    // Convert code to uppercase
                    codeInput.addEventListener('input', function() {
                        this.value = this.value.toUpperCase();
                    });
                });
            </script>
        @endpush
