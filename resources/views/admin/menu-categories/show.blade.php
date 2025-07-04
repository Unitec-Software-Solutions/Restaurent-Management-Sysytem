@extends('layouts.admin')

@section('title', $menuCategory->name . ' - Menu Category')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $menuCategory->name }}</h1>
                <p class="text-gray-600">Menu category details and management</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.menu-categories.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Categories
                </a>
                <a href="{{ route('admin.menu-categories.edit', $menuCategory) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit Category
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Category Details -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Category Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Category Image -->
                        @if($menuCategory->image_url)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category Image</label>
                                <div class="w-full h-48 bg-cover bg-center rounded-lg border border-gray-200" 
                                     style="background-image: url('{{ $menuCategory->image_url }}')"></div>
                            </div>
                        @endif

                        <!-- Basic Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                            <p class="text-gray-900 font-medium">{{ $menuCategory->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                            <p class="text-gray-900">{{ $menuCategory->sort_order }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <span class="px-3 py-1 text-sm rounded-full {{ $menuCategory->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $menuCategory->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Featured</label>
                            <span class="px-3 py-1 text-sm rounded-full {{ $menuCategory->is_featured ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $menuCategory->is_featured ? 'Yes' : 'No' }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                            <p class="text-gray-900">{{ $menuCategory->branch->name ?? 'No Branch Assigned' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                            <p class="text-gray-900">{{ $menuCategory->organization->name ?? 'No Organization Assigned' }}</p>
                        </div>

                        @if($menuCategory->description)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <p class="text-gray-900">{{ $menuCategory->description }}</p>
                            </div>
                        @endif

                        <!-- Timestamps -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Created</label>
                            <p class="text-gray-900">{{ $menuCategory->created_at->format('M j, Y g:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
                            <p class="text-gray-900">{{ $menuCategory->updated_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Items in this Category -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900">Menu Items ({{ $menuCategory->menuItems->count() }})</h2>
                        <a href="{{ route('admin.menu-items.create') }}?category_id={{ $menuCategory->id }}" 
                           class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-sm">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </a>
                    </div>
                </div>
                
                @if($menuCategory->menuItems->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach($menuCategory->menuItems as $item)
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="font-medium text-gray-900">{{ $item->name }}</h3>
                                            <span class="px-2 py-1 text-xs rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($item->type == App\Models\MenuItem::TYPE_KOT)
                                                <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">
                                                    KOT Item
                                                </span>
                                            @endif
                                        </div>
                                        
                                        @if($item->description)
                                            <p class="text-sm text-gray-600 mb-2">{{ $item->description }}</p>
                                        @endif
                                        
                                        <div class="flex items-center gap-4 text-sm text-gray-500">
                                            <span><i class="fas fa-dollar-sign mr-1"></i> ${{ number_format($item->price, 2) }}</span>
                                            @if($item->itemMaster)
                                                <span><i class="fas fa-link mr-1"></i> Linked to Inventory</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.menu-items.show', $item) }}" 
                                           class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1 rounded text-sm">
                                            View
                                        </a>
                                        <a href="{{ route('admin.menu-items.edit', $item) }}" 
                                           class="bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-1 rounded text-sm">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-utensils text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No menu items in this category</h3>
                        <p class="text-gray-500 mb-4">Add menu items to organize your offerings</p>
                        <a href="{{ route('admin.menu-items.create') }}?category_id={{ $menuCategory->id }}" 
                           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add First Menu Item
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Items</span>
                        <span class="font-semibold text-gray-900">{{ $stats['total_menu_items'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Active Items</span>
                        <span class="font-semibold text-green-600">{{ $stats['active_menu_items'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Available Items</span>
                        <span class="font-semibold text-blue-600">{{ $stats['available_menu_items'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.menu-items.create') }}?category_id={{ $menuCategory->id }}" 
                       class="w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-4 py-3 rounded-lg flex items-center text-sm">
                        <i class="fas fa-plus mr-3"></i> Add Menu Item
                    </a>
                    <a href="{{ route('admin.menu-categories.edit', $menuCategory) }}" 
                       class="w-full bg-gray-50 hover:bg-gray-100 text-gray-700 px-4 py-3 rounded-lg flex items-center text-sm">
                        <i class="fas fa-edit mr-3"></i> Edit Category
                    </a>
                    @if($stats['total_menu_items'] == 0)
                        <form method="POST" action="{{ route('admin.menu-categories.destroy', $menuCategory) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this category?')"
                              class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full bg-red-50 hover:bg-red-100 text-red-700 px-4 py-3 rounded-lg flex items-center text-sm">
                                <i class="fas fa-trash mr-3"></i> Delete Category
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Category Status -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Category Status</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Active</span>
                        <span class="px-3 py-1 text-sm rounded-full {{ $menuCategory->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $menuCategory->is_active ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Featured</span>
                        <span class="px-3 py-1 text-sm rounded-full {{ $menuCategory->is_featured ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $menuCategory->is_featured ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Display Order</span>
                        <span class="font-semibold text-gray-900">{{ $menuCategory->sort_order }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
