@extends('layouts.admin')

@section('content')
<div class="p-4 rounded-lg">
            <!-- Header with buttons -->
        <x-nav-buttons :items="[
            ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
            ['name' => 'Items Management', 'link' => route('admin.inventory.items.index')],
            ['name' => 'Stocks Management', 'link' => route('admin.inventory.stock.index')],
            ['name' => 'Transactions Management', 'link' => route('admin.inventory.stock.transactions.index')],
            ['name' => 'Suppliers Management', 'link' => route('admin.suppliers.index')],
            ['name' => 'Payments', 'link' => route('admin.payments.index')],
        ]" active="Suppliers Management" />
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Suppliers</h2>
                <p class="text-sm text-gray-500">Manage your supplier information</p>
            </div>
            <a href="{{ route('admin.suppliers.create') }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i> Add New Supplier
            </a>
        </div>

        <!-- Search & Filters -->
        <div class="p-4 border-b bg-gray-50">
            <form action="{{ route('admin.suppliers.index') }}" method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search suppliers..." 
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="w-full sm:w-auto">
                    <select name="status" 
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="w-full sm:w-auto flex gap-2">
                    <button type="submit" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.suppliers.index') }}" 
                           class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Suppliers Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $supplier->supplier_id }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $supplier->name }}</div>
                                <div class="text-sm text-gray-500">{{ $supplier->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $supplier->contact_person }}</div>
                                <div class="text-sm text-gray-500">{{ $supplier->phone }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">View</a>
                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" 
                                       class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No suppliers found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($suppliers->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection