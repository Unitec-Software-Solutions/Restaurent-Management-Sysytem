@extends('layouts.admin')

@section('title', 'Production Recipes')

@section('header-title', 'Production Recipes')

@section('content')
    <div class="p-4 rounded-lg">
        <!-- Header with navigation buttons -->
        <div class="justify-between items-center mb-4">
            <div class="rounded-lg">
                <x-nav-buttons :items="[
                    ['name' => 'Production', 'link' => route('admin.production.index')],
                    ['name' => 'Production Requests', 'link' => route('admin.production.requests.index')],
                    ['name' => 'Production Orders', 'link' => route('admin.production.orders.index')],
                    ['name' => 'Production Sessions', 'link' => route('admin.production.sessions.index')],
                    ['name' => 'Production Recipes', 'link' => route('admin.production.recipes.index')],
                    ['name' => 'Ingredient Management', 'link' => '#', 'disabled' => true],
                ]" active="Production Recipes" />
            </div>
        </div>

        <!-- Summary Cards -->
        {{-- <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Recipes</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $recipes->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Recipes</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $recipes->where('is_active', true)->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Production Items</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $productionItems->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Avg. Total Time</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $recipes->avg('total_time') ? round($recipes->avg('total_time')) : 0 }}min
                        </p>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Search and Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.production.recipes.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Recipe</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="Enter recipe name" aria-label="Search Recipe" autocomplete="off"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <!-- Production Item Filter -->
                <div>
                    <label for="production_item_id" class="block text-sm font-medium text-gray-700 mb-1">Production
                        Item</label>
                    <select name="production_item_id" id="production_item_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Items</option>
                        @foreach ($productionItems as $item)
                            <option value="{{ $item->id }}"
                                {{ request('production_item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Status Filter -->
                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="is_active" id="is_active"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <!-- Sort By -->
                <div>
                    <label for="sort_by" class="block text-sm font-medium text-gray-400 mb-1">Sort By</label>
                    <select name="sort_by" id="sort_by"
                        class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-400" disabled>
                        <option value="">Default</option>
                        <option value="name_asc" {{ request('sort_by') == 'name_asc' ? 'selected' : '' }}>
                            Name (A-Z)</option>
                        <option value="name_desc" {{ request('sort_by') == 'name_desc' ? 'selected' : '' }}>
                            Name (Z-A)</option>
                        <option value="price_asc" {{ request('sort_by') == 'price_asc' ? 'selected' : '' }}>
                            Price (Low to High)</option>
                        <option value="price_desc" {{ request('sort_by') == 'price_desc' ? 'selected' : '' }}>
                            Price (High to Low)</option>
                    </select>
                </div>
                <!-- Filter Buttons -->

                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('admin.production.recipes.index') }}"
                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Production Recipes List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Production Recipes</h2>
                    <p class="text-sm text-gray-500">
                        Showing {{ $recipes->firstItem() ?? 0 }} to {{ $recipes->lastItem() ?? 0 }} of
                        {{ $recipes->total() }} recipes
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        @if (Auth::guard('admin')->user()->is_super_admin)
                            Organization: All Organizations (Super Admin)
                        @elseif(Auth::guard('admin')->user()->organization)
                            Organization: {{ Auth::guard('admin')->user()->organization->name }}
                        @else
                            Organization: Not Assigned
                        @endif
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('admin.production.recipes.create') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Recipe
                    </a>
                </div>
            </div>

            <!-- Quick Filter Buttons -->
            <div class="border-b px-6 pt-4 pb-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.production.recipes.index') }}"
                    class="px-3 py-1 text-sm rounded-full {{ !request()->hasAny(['is_active', 'production_item_id', 'search']) ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All
                </a>
                <a href="{{ route('admin.production.recipes.index', ['is_active' => '1']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('is_active') === '1' ? 'bg-green-200 text-green-900 font-medium' : 'bg-green-100 hover:bg-green-200 text-green-800' }}">
                    Active ({{ $recipes->where('is_active', true)->count() }})
                </a>
                <a href="{{ route('admin.production.recipes.index', ['is_active' => '0']) }}"
                    class="px-3 py-1 text-sm rounded-full {{ request('is_active') === '0' ? 'bg-red-200 text-red-900 font-medium' : 'bg-red-100 hover:bg-red-200 text-red-800' }}">
                    Inactive ({{ $recipes->where('is_active', false)->count() }})
                </a>
                @if (request()->hasAny(['is_active', 'production_item_id', 'search']))
                    <a href="{{ route('admin.production.recipes.index') }}"
                        class="px-3 py-1 text-sm bg-red-100 hover:bg-red-200 text-red-800 rounded-full">
                        <i class="fas fa-times mr-1"></i>Clear Filters
                    </a>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Recipe Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Production Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yield
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ingredients</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($recipes as $recipe)
                            <tr class="hover:bg-gray-50 cursor-pointer"
                                onclick="window.location='{{ route('admin.production.recipes.show', $recipe) }}'">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-indigo-600">{{ $recipe->recipe_name }}</div>
                                    @if ($recipe->difficulty_level)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-1
                                            @if ($recipe->difficulty_level == 'Easy') bg-green-100 text-green-800
                                            @elseif($recipe->difficulty_level == 'Medium') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ $recipe->difficulty_level }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $recipe->productionItem->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $recipe->productionItem->unit_of_measurement ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ number_format($recipe->yield_quantity, 2) }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $recipe->productionItem->unit_of_measurement ?? 'units' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($recipe->preparation_time || $recipe->cooking_time)
                                        <div class="text-sm text-gray-900">Prep: {{ $recipe->preparation_time ?? 0 }}min
                                        </div>
                                        <div class="text-sm text-gray-900">Cook: {{ $recipe->cooking_time ?? 0 }}min</div>
                                        <div class="font-medium text-indigo-600">Total: {{ $recipe->total_time }}min</div>
                                    @else
                                        <span class="text-sm text-gray-400">Not specified</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $recipe->details->count() }} ingredients</div>
                                    @if ($recipe->details->count() > 0)
                                        <div class="text-sm text-gray-500">
                                            {{ $recipe->details->take(2)->pluck('rawMaterialItem.name')->implode(', ') }}
                                            @if ($recipe->details->count() > 2)
                                                <span class="text-blue-600">+{{ $recipe->details->count() - 2 }}
                                                    more</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $recipe->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $recipe->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('admin.production.recipes.show', $recipe) }}"
                                            class="text-indigo-600 hover:text-indigo-800" title="View Recipe">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.production.recipes.edit', $recipe) }}"
                                            class="text-blue-600 hover:text-blue-800" title="Edit Recipe">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.production.recipes.toggle-status', $recipe) }}"
                                            method="POST" class="inline"
                                            onsubmit="event.stopPropagation(); return confirm('Are you sure you want to {{ $recipe->is_active ? 'deactivate' : 'activate' }} this recipe?');">
                                            @csrf
                                            <button type="submit"
                                                class="{{ $recipe->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}"
                                                title="{{ $recipe->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i
                                                    class="fas {{ $recipe->is_active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-book text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">No recipes found</p>
                                    <p class="text-sm">Create your first recipe to get started</p>
                                    <a href="{{ route('admin.production.recipes.create') }}"
                                        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                                        <i class="fas fa-plus mr-2"></i>Create Recipe
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($recipes->hasPages())
                <div class="px-6 py-4 bg-white border-t border-gray-200">
                    {{ $recipes->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
