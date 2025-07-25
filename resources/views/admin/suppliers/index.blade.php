@extends('layouts.admin')
@section('header-title', 'Suppliers Management')
@section('content')

    <div class="mx-auto px-4 py-8">


            <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Supplier Management</h1>
                <p class="text-gray-600 mt-1">Manage suppliers and their details</p>
            </div>
                <a href="{{ route('admin.suppliers.create') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Supplier
                    {{-- <x-lucide-user-plus class="w-5 h-5 text-white mr-2" /> Add New Supplier --}}
                </a>
        </div>
    </div>

        {{-- <!-- Header with buttons -->
        <x-nav-buttons :items="[
            ['name' => 'Suppliers Management', 'link' => route('admin.suppliers.index')],
            ['name' => 'Purchase Orders', 'link' => route('admin.purchase-orders.index')],
            ['name' => 'Supplier GRNs', 'link' => route('admin.grn.index')],
            ['name' => 'Supplier Payments', 'link' => '#', 'disabled' => true],
        ]" active="Suppliers Management" /> --}}

        {{-- Debug Info Card --}}
        {{-- @if(config('app.debug'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-medium text-blue-800">🔍 Suppliers Debug Info</h3>
                    <a href="{{ route('admin.suppliers.index', ['debug' => 1]) }}"
                        class="text-xs text-blue-600 hover:text-blue-800">
                        Full Debug (debug=1)
                    </a>
                </div>
                <div class="text-xs text-blue-700 mt-2 grid grid-cols-2 gap-4">
                    <div>
                        <p><strong>Suppliers Variable:</strong>
                            {{ isset($suppliers) ? 'Set (' . get_class($suppliers) . ')' : 'NOT SET' }}</p>
                        <p><strong>Suppliers Count:</strong> {{ isset($suppliers) ? $suppliers->count() : 'N/A' }}</p>
                        <p><strong>Total in View:</strong> {{ isset($totalSuppliers) ? $totalSuppliers : 'NOT SET' }}</p>
                    </div>
                    <div>
                        <p><strong>Total DB Count:</strong> {{ \App\Models\Supplier::count() }}</p>
                        <p><strong>User Org ID:</strong> {{ auth()->user()->organization_id ?? 'None' }}</p>
                        <p><strong>Request Filters:</strong> search={{ request('search', 'none') }},
                            status={{ request('status', 'none') }}</p>
                    </div>
                </div>
            </div>
        @endif --}}

                    <!-- Filters with Export -->
            <x-module-filters
                :action="route('admin.suppliers.index')"
                :export-permission="'export_suppliers'"
                :export-filename="'suppliers_export.xlsx'">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </x-module-filters>


        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            {{-- <div class="p-6 border-b flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Suppliers</h2>
                    <p class="text-sm text-gray-500">
                        @if (isset($suppliers))
                            @if (method_exists($suppliers, 'total'))
                                Showing {{ $suppliers->firstItem() ?? 1 }} to
                                {{ $suppliers->lastItem() ?? $suppliers->count() }} of {{ $suppliers->total() }} suppliers
                            @else
                                Showing {{ $suppliers->count() }} suppliers
                            @endif
                        @else
                            <span class="text-red-500">⚠ No suppliers data available</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.suppliers.create') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Supplier
                </a>
            </div> --}}



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
                        @if (isset($suppliers))
                            @forelse($suppliers as $supplier)
                                <tr class="hover:bg-gray-50 cursor-pointer"
                                    onclick="window.location='{{ route('admin.suppliers.show', $supplier) }}'">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $supplier->supplier_id ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $supplier->name ?? 'No Name' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $supplier->email ?? 'No Email' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $supplier->contact_person ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $supplier->phone ?? 'No Phone' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ ($supplier->is_active ?? false) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ($supplier->is_active ?? false) ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('admin.suppliers.show', $supplier) }}"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                onclick="event.stopPropagation()" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.suppliers.edit', $supplier) }}"
                                                class="text-blue-600 hover:text-blue-900" onclick="event.stopPropagation()"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center">
                                        <div class="text-gray-400 text-2xl mb-2">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">No suppliers found</h3>
                                        <p class="text-gray-500">
                                            @if (request()->hasAny(['search', 'status']))
                                                Try adjusting your search criteria or <a
                                                    href="{{ route('admin.suppliers.index') }}"
                                                    class="text-indigo-600 hover:text-indigo-800">clear filters</a>.
                                            @else
                                                Get started by creating your first supplier.
                                            @endif
                                        </p>
                                        <div class="mt-4">
                                            <a href="{{ route('admin.suppliers.create') }}"
                                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                                                <i class="fas fa-plus mr-2"></i> Create Supplier
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center">
                                    <div class="text-red-400 text-2xl mb-2">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-red-600 mb-1">Variable Error</h3>
                                    <p class="text-red-500">The $suppliers variable is not defined in this view.</p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Check the SupplierController@index method to ensure data is being passed correctly.
                                    </p>
                                    <div class="mt-4">
                                        <a href="{{ route('admin.suppliers.index', ['debug' => 1]) }}"
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                                            <i class="fas fa-bug mr-2"></i> Debug This Issue
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if (isset($suppliers) && method_exists($suppliers, 'hasPages') && $suppliers->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $suppliers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
