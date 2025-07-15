@extends('layouts.admin')

@section('title', 'View Item Category')
@section('header-title', 'View Item Category - ' . $category->name)
@section('page-header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $category->name }}</h1>
            <p class="text-gray-600">Category Details</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.item-categories.edit', $category) }}"
                class="bg-[#FF9800] hover:bg-[#e68a00] text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit Category
            </a>
            <a href="{{ route('admin.item-categories.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Categories
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Category Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Category Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Category Name</label>
                            <p class="text-sm font-medium text-gray-900">{{ $category->name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Category Code</label>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                {{ $category->code }}
                            </span>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                            <p class="text-sm text-gray-900">{{ $category->description ?? 'No description provided' }}</p>
                        </div>
                        @if (Auth::guard('admin')->user()->is_super_admin)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Organization</label>
                                <p class="text-sm text-gray-900">{{ $category->organization->name ?? 'Not Assigned' }}</p>
                            </div>
                        @endif
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                            @if ($category->is_active)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                    Active
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                    Inactive
                                </span>
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Created Date</label>
                            <p class="text-sm text-gray-900">{{ $category->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Last Updated</label>
                            <p class="text-sm text-gray-900">{{ $category->updated_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items in Category -->
            <div class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900">Items in Category</h2>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                            {{ $category->items->count() }} items
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    @if ($category->items->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-50 border-b">
                                        <th class="text-left py-2 px-3 font-medium text-gray-700">Item Name</th>
                                        <th class="text-left py-2 px-3 font-medium text-gray-700">Code</th>
                                        <th class="text-left py-2 px-3 font-medium text-gray-700">Unit</th>
                                        <th class="text-left py-2 px-3 font-medium text-gray-700">Current Stock</th>
                                        <th class="text-left py-2 px-3 font-medium text-gray-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($category->items as $item)
                                        <tr class="border-b hover:bg-gray-50 transition-colors">
                                            <td class="py-2 px-3">
                                                <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                            </td>
                                            <td class="py-2 px-3">
                                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">
                                                    {{ $item->item_code ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-3">
                                                <span
                                                    class="text-gray-600">{{ $item->unit_of_measurement ?? 'N/A' }}</span>
                                            </td>
                                            <td class="py-2 px-3">
                                                <span class="text-gray-600">{{ $item->current_stock ?? 0 }}</span>
                                            </td>
                                            <td class="py-2 px-3">
                                                @if ($item->is_active ?? true)
                                                    <span
                                                        class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                                        Active
                                                    </span>
                                                @else
                                                    <span
                                                        class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-box text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No items in this category</p>
                            <p class="text-gray-400 text-sm">Items will appear here when they are added to this category</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Quick Stats</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Items</span>
                            <span class="font-semibold text-gray-900">{{ $category->items->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Active Items</span>
                            <span
                                class="font-semibold text-green-600">{{ $category->items->where('is_active', true)->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Inactive Items</span>
                            <span
                                class="font-semibold text-red-600">{{ $category->items->where('is_active', false)->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('admin.item-categories.edit', $category) }}"
                            class="w-full bg-[#FF9800] hover:bg-[#e68a00] text-white px-4 py-2 rounded-lg font-medium transition-colors text-center block">
                            <i class="fas fa-edit mr-2"></i>Edit Category
                        </a>
                        @if ($category->items->count() == 0)
                            <button onclick="deleteCategory({{ $category->id }})"
                                class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-trash mr-2"></i>Delete Category
                            </button>
                        @else
                            <div class="w-full bg-gray-300 text-gray-500 px-4 py-2 rounded-lg font-medium text-center">
                                <i class="fas fa-trash mr-2"></i>Cannot Delete (Has Items)
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3"></i>
                <h3 class="text-lg font-semibold">Confirm Deletion</h3>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this category? This action cannot be undone.</p>
            <div class="flex gap-3 justify-end">
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button id="confirmDelete"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Delete
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let categoryToDelete = null;

        function deleteCategory(id) {
            categoryToDelete = id;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            categoryToDelete = null;
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }

        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (categoryToDelete) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/item-categories/${categoryToDelete}`;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';

                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>
@endpush
