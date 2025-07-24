@extends('layouts.admin')

@section('title', 'Inventory Reports Dashboard')
@section('header-title', 'Inventory Reports Dashboard')

@section('content')
    <div class="mx-auto px-4 py-8">
        <!-- Navigation Buttons -->
        <div class="mb-8 rounded-lg">
            <x-nav-buttons :items="[
                ['name' => 'Inventory Report', 'link' => route('admin.reports.inventory.index')],
                ['name' => 'Stock Report', 'link' => route('admin.reports.inventory.stock')],
                ['name' => 'Category Report', 'link' => route('admin.reports.inventory.category')],
                ['name' => 'Goods Transfer Note Report', 'link' => route('admin.reports.inventory.gtn')],
                ['name' => 'Goods Receipt Note Report', 'link' => route('admin.reports.inventory.grn')],
                ['name' => 'Stock Release Note Report', 'link' => route('admin.reports.inventory.srn')],
            ]" active="Inventory Report" />
        </div>

        <!-- Reports Dashboard -->
        <div class="space-y-6">
            <!-- Section 1: Inventory Overview -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Inventory Overview Reports</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Stock Report -->
                    <a href="{{ route('admin.reports.inventory.stock') }}"
                       class="group p-4 border rounded-lg hover:bg-blue-50 hover:border-blue-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-box text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 group-hover:text-blue-600">Stock Report</h3>
                                <p class="text-sm text-gray-500">Current stock levels and status</p>
                            </div>
                        </div>
                    </a>

                    <!-- Category Report -->
                    <a href="{{ route('admin.reports.inventory.category') }}"
                       class="group p-4 border rounded-lg hover:bg-purple-50 hover:border-purple-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-tags text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 group-hover:text-purple-600">Category Report</h3>
                                <p class="text-sm text-gray-500">Inventory by categories</p>
                            </div>
                        </div>
                    </a>

                    <!-- Inventory Report -->
                    <a href="{{ route('admin.reports.inventory.index') }}"
                       class="group p-4 border rounded-lg hover:bg-green-50 hover:border-green-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-clipboard-list text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 group-hover:text-green-600">Inventory Report</h3>
                                <p class="text-sm text-gray-500">Complete inventory analysis</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Section 2: Transaction Reports -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Transaction Reports</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Goods Receipt Note -->
                    <a href="{{ route('admin.reports.inventory.grn') }}"
                       class="group p-4 border rounded-lg hover:bg-orange-50 hover:border-orange-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="bg-orange-100 p-3 rounded-full">
                                <i class="fas fa-truck-loading text-orange-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 group-hover:text-orange-600">Goods Receipt Notes</h3>
                                <p class="text-sm text-gray-500">Received goods documentation</p>
                            </div>
                        </div>
                    </a>

                    <!-- Goods Transfer Note -->
                    <a href="{{ route('admin.reports.inventory.gtn') }}"
                       class="group p-4 border rounded-lg hover:bg-indigo-50 hover:border-indigo-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="bg-indigo-100 p-3 rounded-full">
                                <i class="fas fa-exchange-alt text-indigo-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 group-hover:text-indigo-600">Goods Transfer Notes</h3>
                                <p class="text-sm text-gray-500">Inventory transfers between branches</p>
                            </div>
                        </div>
                    </a>

                    <!-- Stock Release Note -->
                    <a href="{{ route('admin.reports.inventory.srn') }}"
                       class="group p-4 border rounded-lg hover:bg-red-50 hover:border-red-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="bg-red-100 p-3 rounded-full">
                                <i class="fas fa-external-link-alt text-red-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 group-hover:text-red-600">Stock Release Notes</h3>
                                <p class="text-sm text-gray-500">Released stock documentation</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>


        </div>
    </div>
@endsection

