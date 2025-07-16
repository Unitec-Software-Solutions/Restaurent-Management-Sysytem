{{-- Revert this file and the related Dashboard controller later --}}
{{-- app/Http/Controllers/Admin/ItemDashboardController.php --}}
@extends('layouts.admin')

@section('title', 'Item Inventory Dashboard')
@section('header-title', 'Item Inventory Dashboard')

@section('content')
    <div class="p-4 space-y-8">
        <!-- Navigation Buttons -->
        <div class="rounded-lg">
            <x-nav-buttons :items="[
                ['name' => 'Dashboard', 'link' => route('admin.inventory.dashboard')],
                ['name' => 'Item Management', 'link' => route('admin.inventory.items.index')],
                ['name' => 'Stock Management', 'link' => route('admin.inventory.stock.index')],
                ['name' => 'Goods Received Notes', 'link' => route('admin.grn.index')],
                ['name' => 'Transfer Notes', 'link' => route('admin.inventory.gtn.index')],
                ['name' => 'Transactions', 'link' => route('admin.inventory.stock.transactions.index')],
            ]" active="Dashboard" />
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Item Management --}}
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Item Management</h2>
                    <p class="text-gray-500 text-sm">Manage inventory items and their details</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.inventory.items.index') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center shadow-sm">
                        <i class="fas fa-file-alt mr-2"></i> View All Items
                    </a>
                    <a href="{{ route('admin.inventory.items.create') }}"
                        class="bg-white border border-indigo-600 text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg flex items-center shadow-sm">
                        <i class="fas fa-plus mr-2"></i> New Item
                    </a>
                </div>
            </div>

            {{-- item categories --}}
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Item Categories</h2>
                    <p class="text-gray-500 text-sm">Manage item categories and their details</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.item-categories.index') }}"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center shadow-sm">
                        <i class="fas fa-file-alt mr-2"></i> View All Categories
                    </a>
                    <a href="{{ route('admin.item-categories.create') }}"
                        class="bg-white border border-indigo-600 text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg flex items-center shadow-sm">
                        <i class="fas fa-plus mr-2"></i> New Category
                    </a>
                </div>
            </div>

            {{-- Stock Management --}}
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Stock Management</h2>
                    <p class="text-gray-500 text-sm">Monitor and update item stock levels</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.inventory.stock.index') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-box mr-2"></i> View Stock
                    </a>

                    <a href="{{ route('admin.inventory.stock.transactions.index') }}"
                        class="bg-green-50 border border-green-600 text-green-700 hover:bg-green-100 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-history mr-2"></i> Stock Transactions
                    </a>

                    {{-- <a href="{{ route('admin.inventory.stock.create') }}"
                        class="bg-white border border-red-600 text-red-600 hover:bg-red-50 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Stock { dev }
                    </a> --}}
                </div>
            </div>

            {{-- Stock Management --}}
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Stock Release</h2>
                    <p class="text-gray-500 text-sm">Stock Release and Management</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.inventory.stock.index') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-box mr-2"></i> View Stock levels
                    </a>

                    <a href="{{ route('admin.inventory.stock.transactions.index') }}"
                        class="bg-green-50 border border-green-600 text-green-700 hover:bg-green-100 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-history mr-2"></i> relese stock 
                    </a>

                    {{-- <a href="{{ route('admin.inventory.stock.create') }}"
                        class="bg-white border border-red-600 text-red-600 hover:bg-red-50 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Stock { dev }
                    </a> --}}
                </div>
            </div>

            {{-- Goods Received Notes --}}
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Goods Received Notes</h2>
                    <p class="text-gray-500 text-sm">Track and verify received goods</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.grn.index') }}"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-truck-loading mr-2"></i> View GRNs
                    </a>
                    <a href="{{ route('admin.grn.create') }}"
                        class="bg-white border border-yellow-600 text-yellow-600 hover:bg-yellow-50 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> New GRN
                    </a>
                </div>
            </div>

            {{-- Transfer Notes --}}
            <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Transfer Notes</h2>
                    <p class="text-gray-500 text-sm">Manage inventory transfers between branches</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.inventory.gtn.index') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-exchange-alt mr-2"></i> View Transfers
                    </a>
                    <a href="{{ route('admin.inventory.gtn.create') }}"
                        class="bg-white border border-blue-600 text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Transfer
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
