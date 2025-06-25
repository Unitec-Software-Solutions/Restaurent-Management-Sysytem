@extends('layouts.admin')

@section('title', 'Recipe Management')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Recipe Management</h1>
                <p class="text-gray-600 mt-1">Manage production recipes and bill of materials</p>
            </div>
            <a href="{{ route('admin.production.recipes.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                <i class="fas fa-plus mr-2"></i>Create Recipe
            </a>
        </div>

        <div class="px-4 py-8">
            <!-- Summary Cards -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
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

                <div class="bg-white rounded-lg shadow p-6">
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

                <div class="bg-white rounded-lg shadow p-6">
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

                <div class="bg-white rounded-lg shadow p-6">
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
            </div>

        </div>



        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.production.recipes.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Production Item</label>
                    <select name="production_item_id" class="w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="">All Items</option>
                        @foreach ($productionItems as $item)
                            <option value="{{ $item->id }}"
                                {{ request('production_item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="is_active" class="w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </div>

                <div class="flex items-end justify-end">
                    <a href="{{ route('admin.production.recipes.index') }}" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-times mr-1"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Recipes Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Recipe</th>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($recipes as $recipe)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $recipe->recipe_name }}</div>
                                        @if ($recipe->difficulty_level)
                                            <div class="text-sm text-gray-500">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if ($recipe->difficulty_level == 'Easy') bg-green-100 text-green-800
                                                    @elseif($recipe->difficulty_level == 'Medium') bg-yellow-100 text-yellow-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ $recipe->difficulty_level }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $recipe->productionItem->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $recipe->productionItem->unit_of_measurement ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ number_format($recipe->yield_quantity, 2) }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $recipe->productionItem->unit_of_measurement ?? 'units' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        @if ($recipe->preparation_time || $recipe->cooking_time)
                                            <div>Prep: {{ $recipe->preparation_time ?? 0 }}min</div>
                                            <div>Cook: {{ $recipe->cooking_time ?? 0 }}min</div>
                                            <div class="font-medium">Total: {{ $recipe->total_time }}min</div>
                                        @else
                                            <span class="text-gray-500">Not specified</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ $recipe->details->count() }} ingredients
                                    </div>
                                    @if ($recipe->details->count() > 0)
                                        <div class="text-xs text-gray-500">
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
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $recipe->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $recipe->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.production.recipes.show', $recipe) }}"
                                            class="text-blue-600 hover:text-blue-900" title="View Recipe">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.production.recipes.edit', $recipe) }}"
                                            class="text-indigo-600 hover:text-indigo-900" title="Edit Recipe">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.production.recipes.toggle-status', $recipe) }}"
                                            method="POST" class="inline">
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
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <i class="fas fa-book text-4xl mb-4"></i>
                                        <p class="text-lg font-medium">No recipes found</p>
                                        <p class="text-sm">Create your first recipe to get started</p>
                                        <a href="{{ route('admin.production.recipes.create') }}"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            <i class="fas fa-plus mr-2"></i>Create Recipe
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($recipes->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $recipes->appends(request()->query())->links() }}
                </div>
            @endif
        </div>


    </div>
@endsection
