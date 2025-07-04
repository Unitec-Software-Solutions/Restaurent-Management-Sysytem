@extends('layouts.admin')

@section('title', 'Menu Categories')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Menu Categories</h1>
                <p class="text-gray-600">Organize your menu items into categories</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.menu-categories.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Category
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.menu-categories.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search categories..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Branch Filter -->
            @if($branches->count() > 1)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Filter Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.menu-categories.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Categories Grid -->
    @if($categories->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
            @foreach($categories as $category)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                    <!-- Category Image -->
                    @if($category->image_url)
                        <div class="h-32 bg-cover bg-center rounded-t-lg" style="background-image: url('{{ $category->image_url }}')"></div>
                    @else
                        <div class="h-32 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-t-lg flex items-center justify-center">
                            <i class="fas fa-utensils text-4xl text-indigo-400"></i>
                        </div>
                    @endif

                    <div class="p-4">
                        <!-- Category Header -->
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $category->name }}</h3>
                            <div class="flex gap-1">
                                <!-- Status Badge -->
                                <span class="px-2 py-1 text-xs rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <!-- Featured Badge -->
                                @if($category->is_featured)
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                        Featured
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Description -->
                        @if($category->description)
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $category->description }}</p>
                        @endif

                        <!-- Stats -->
                        <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                            <span><i class="fas fa-utensils mr-1"></i> {{ $category->menu_items_count }} items</span>
                            <span><i class="fas fa-sort-numeric-down mr-1"></i> Order: {{ $category->sort_order }}</span>
                        </div>

                        <!-- Branch Info -->
                        <div class="text-xs text-gray-500 mb-4">
                            <i class="fas fa-store mr-1"></i> {{ $category->branch->name ?? 'No Branch' }}
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a href="{{ route('admin.menu-categories.show', $category) }}" 
                               class="flex-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-2 rounded text-sm text-center transition-colors">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            <a href="{{ route('admin.menu-categories.edit', $category) }}" 
                               class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-2 rounded text-sm text-center transition-colors">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            {{ $categories->appends(request()->query())->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-tags text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No categories found</h3>
            <p class="text-gray-500 mb-6">Start organizing your menu by creating categories</p>
            <a href="{{ route('admin.menu-categories.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Create First Category
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on select change
    const selects = document.querySelectorAll('select[name="status"], select[name="branch_id"]');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>
@endpush
@endsection
