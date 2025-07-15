@extends('layouts.admin')

@section('title', 'Recipe Details')
@section('header-title', 'Recipe Details - ' . $recipe->recipe_name)
@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $recipe->recipe_name }}</h1>
                    <p class="text-gray-600 mt-1">Recipe for {{ $recipe->productionItem->name ?? 'Production Item' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.production.recipes.edit', $recipe) }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-edit mr-2"></i>Edit Recipe
                    </a>
                    <a href="{{ route('admin.production.recipes.index') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Recipes
                    </a>
                </div>
            </div>

            <!-- Recipe Overview -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="bg-blue-100 rounded-full p-3 w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <i class="fas fa-box text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Yield</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($recipe->yield_quantity, 2) }}</p>
                        <p class="text-sm text-gray-500">{{ $recipe->productionItem->unit_of_measurement ?? 'units' }}</p>
                    </div>

                    <div class="text-center">
                        <div class="bg-green-100 rounded-full p-3 w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <i class="fas fa-clock text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Prep Time</h3>
                        <p class="text-2xl font-bold text-green-600">{{ $recipe->preparation_time ?? 0 }}</p>
                        <p class="text-sm text-gray-500">minutes</p>
                    </div>

                    <div class="text-center">
                        <div class="bg-orange-100 rounded-full p-3 w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <i class="fas fa-fire text-orange-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Cook Time</h3>
                        <p class="text-2xl font-bold text-orange-600">{{ $recipe->cooking_time ?? 0 }}</p>
                        <p class="text-sm text-gray-500">minutes</p>
                    </div>

                    <div class="text-center">
                        <div class="bg-purple-100 rounded-full p-3 w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <i class="fas fa-chart-line text-purple-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Difficulty</h3>
                        <p class="text-lg font-bold">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($recipe->difficulty_level == 'Easy') bg-green-100 text-green-800
                                @elseif($recipe->difficulty_level == 'Medium') bg-yellow-100 text-yellow-800
                                @elseif($recipe->difficulty_level == 'Hard') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $recipe->difficulty_level ?? 'Not Set' }}
                            </span>
                        </p>
                    </div>
                </div>

                @if ($recipe->description)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Description</h4>
                        <p class="text-gray-700">{{ $recipe->description }}</p>
                    </div>
                @endif
            </div>

            <!-- Recipe Status -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Recipe Status</h3>
                        <p class="text-sm text-gray-500">Current status and activity</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $recipe->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas {{ $recipe->is_active ? 'fa-check-circle' : 'fa-ban' }} mr-2"></i>
                            {{ $recipe->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <form action="{{ route('admin.production.recipes.toggle-status', $recipe) }}" method="POST"
                            class="inline">
                            @csrf
                            <button type="submit"
                                class="bg-{{ $recipe->is_active ? 'red' : 'green' }}-600 hover:bg-{{ $recipe->is_active ? 'red' : 'green' }}-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                <i class="fas {{ $recipe->is_active ? 'fa-ban' : 'fa-check-circle' }} mr-2"></i>
                                {{ $recipe->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Ingredients List -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Ingredients & Raw Materials</h3>
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                        {{ $recipe->details->count() }} ingredients
                    </span>
                </div>

                @if ($recipe->details->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingredient
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preparation
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($recipe->details as $index => $detail)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $detail->rawMaterialItem->name ?? 'Unknown Item' }}
                                            </div>
                                            @if ($detail->rawMaterialItem->category)
                                                <div class="text-xs text-gray-500">
                                                    {{ $detail->rawMaterialItem->category->name }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ number_format($detail->quantity_required, 3) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $detail->unit_of_measurement ?: $detail->rawMaterialItem->unit_of_measurement ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $detail->preparation_notes ?: 'No notes' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
                        <p class="text-lg font-medium">No ingredients defined</p>
                        <p class="text-sm">Add ingredients to complete this recipe</p>
                    </div>
                @endif
            </div>

            <!-- Instructions -->
            @if ($recipe->instructions)
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Instructions</h3>
                    <div class="prose max-w-none">
                        {!! nl2br(e($recipe->instructions)) !!}
                    </div>
                </div>
            @endif

            <!-- Additional Notes -->
            @if ($recipe->notes)
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Notes</h3>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="text-yellow-800">
                            {!! nl2br(e($recipe->notes)) !!}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recipe Metadata -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recipe Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created On</label>
                        <p class="text-sm text-gray-900">{{ $recipe->created_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <p class="text-sm text-gray-900">{{ $recipe->updated_at->format('F d, Y \a\t g:i A') }}</p>
                    </div>

                    @if ($recipe->createdBy)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Created By</label>
                            <p class="text-sm text-gray-900">{{ $recipe->createdBy->name ?? 'Unknown' }}</p>
                        </div>
                    @endif

                    @if ($recipe->updatedBy)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Updated By</label>
                            <p class="text-sm text-gray-900">{{ $recipe->updatedBy->name ?? 'Unknown' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
