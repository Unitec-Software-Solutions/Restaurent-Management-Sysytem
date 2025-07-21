@extends('layouts.admin')

@section('title', 'Item Categories')
@section('header-title', 'Item Categories')
@section('content')
<div class="mx-auto px-4 py-8">
            <!-- Navigation Buttons -->
        <div class="rounded-lg">
            <x-nav-buttons :items="[
                ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
                ['name' => 'Item Management', 'link' => route('admin.inventory.items.index')],
                ['name' => 'Stock Management', 'link' => route('admin.inventory.stock.index')],
                ['name' => 'Stock Release Notes', 'link' => route('admin.inventory.srn.index')],
                ['name' => 'Goods Received Notes', 'link' => route('admin.grn.index')],
                ['name' => 'Goods Transfer Notes', 'link' => route('admin.inventory.gtn.index')],
                ['name' => 'Transactions', 'link' => route('admin.inventory.stock.transactions.index')],
            ]" active=" " />
        </div>
        
    <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
                <p class="text-gray-600 mt-1">Manage item categories and their details</p>
            </div>
            @if(auth('admin')->user()->isSuperAdmin())
                <a href="{{ route('admin.item-categories.create') }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Create Category
                </a>
            @endif
        </div>
    </div>




        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <!-- Search and Filter -->
                <div class="flex flex-col sm:flex-row gap-4 mb-6">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" id="search" placeholder="Search categories..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF9800] focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <select id="status-filter"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF9800] focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Categories Table -->
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Name</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Code</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Description</th>
                                @if (Auth::guard('admin')->user()->is_super_admin)
                                    <th class="text-left py-3 px-4 font-medium text-gray-700">Organization</th>
                                @endif
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Items Count</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Status</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr class="border-b hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900">{{ $category->name }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                            {{ $category->code }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-gray-600 max-w-xs truncate">
                                            {{ $category->description ?? 'No description' }}
                                        </div>
                                    </td>
                                    @if (Auth::guard('admin')->user()->is_super_admin)
                                        <td class="py-3 px-4">
                                            <div class="text-gray-600">
                                                {{ $category->organization->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                    @endif
                                    <td class="py-3 px-4">
                                        <span class="text-gray-600">{{ $category->items_count ?? 0 }} items</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($category->is_active)
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
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.item-categories.show', $category) }}"
                                                class="text-blue-600 hover:text-blue-800 transition-colors" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.item-categories.edit', $category) }}"
                                                class="text-indigo-600 hover:text-indigo-800 transition-colors"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteCategory({{ $category->id }})"
                                                class="text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::guard('admin')->user()->is_super_admin ? '7' : '6' }}"
                                        class="py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-tags text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium">No categories found</p>
                                            <p class="text-sm text-gray-400 mb-4">Get started by creating your first item
                                                category</p>
                                            <a href="{{ route('admin.item-categories.create') }}"
                                                class="bg-[#FF9800] hover:bg-[#e68a00] text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                                <i class="fas fa-plus mr-2"></i>Add Category
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($categories->hasPages())
                    <div class="mt-6">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex items-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3"></i>
                    <h3 class="text-lg font-semibold">Confirm Deletion</h3>
                </div>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this category? This action cannot be undone.
                </p>
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

            // Search functionality
            document.getElementById('search').addEventListener('input', function(e) {
                // Add search logic here if needed
            });

            // Status filter
            document.getElementById('status-filter').addEventListener('change', function(e) {
                // Add filter logic here if needed
            });
        </script>
    @endpush
