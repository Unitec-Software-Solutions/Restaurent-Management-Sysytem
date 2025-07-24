@extends('layouts.admin')
@section('header-title', 'Current Stock Report')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg mb-6">
        <x-nav-buttons :items="[
            ['name' => 'Reports Dashboard', 'link' => route('admin.reports.index')],
            ['name' => 'Stock Reports', 'link' => route('admin.reports.inventory.stock')],
            ['name' => 'Current Stock', 'link' => route('admin.reports.inventory.stock.current')],
            ['name' => 'Stock Movement', 'link' => route('admin.reports.inventory.stock.movement')],
            ['name' => 'Stock Valuation', 'link' => route('admin.reports.inventory.stock.valuation')],
        ]" active="Current Stock" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Current Stock Report</h1>
                <p class="text-gray-600">Real-time inventory levels across all branches with reorder alerts and stock values</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.stock.current', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>
                <a href="{{ route('admin.reports.inventory.stock.current', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-module-filters 
        :branches="$branches ?? []"
        :selectedBranch="$branchId ?? ''"
        :customFilters="[
            [
                'name' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'placeholder' => 'All Categories',
                'options' => isset($categories) ? $categories->pluck('name', 'id')->toArray() : []
            ],
            [
                'name' => 'stock_status',
                'label' => 'Stock Status',
                'type' => 'select',
                'placeholder' => 'All Items',
                'options' => [
                    'low_stock' => 'Low Stock',
                    'out_of_stock' => 'Out of Stock',
                    'in_stock' => 'In Stock',
                    'overstock' => 'Overstock'
                ]
            ],
            [
                'name' => 'min_value',
                'label' => 'Min Value',
                'type' => 'number',
                'placeholder' => '0.00',
                'step' => '0.01'
            ],
            [
                'name' => 'max_value',
                'label' => 'Max Value',
                'type' => 'number',
                'placeholder' => '999999.99',
                'step' => '0.01'
            ]
        ]"
        showBranchFilter="true"
    />

    <!-- Report Results -->
    @if(isset($reportData) && count($reportData['data']) > 0)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-boxes text-indigo-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Items</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_items'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-warehouse text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Stock</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_stock']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Stock Value</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_value'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Low Stock</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['low_stock_items'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-times-circle text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Out of Stock</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['out_of_stock_items'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Levels</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost & Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['data'] as $stock)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $stock['item_name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $stock['item_code'] ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-400">{{ $stock['category'] ?? 'Uncategorized' }}</div>
                                    <div class="text-xs text-gray-400">Unit: {{ $stock['unit'] ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $stock['branch_name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $stock['branch_code'] ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-semibold
                                        @if($stock['current_stock'] <= 0) text-red-600
                                        @elseif($stock['current_stock'] <= $stock['reorder_level']) text-yellow-600
                                        @else text-green-600 @endif">
                                        {{ number_format($stock['current_stock'], 2) }}
                                    </div>
                                    @if($stock['reserved_stock'] > 0)
                                        <div class="text-xs text-blue-600">
                                            Reserved: {{ number_format($stock['reserved_stock'], 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Min: {{ number_format($stock['minimum_stock'], 2) }}
                                    </div>
                                    <div class="text-sm text-gray-900">
                                        Reorder: {{ number_format($stock['reorder_level'], 2) }}
                                    </div>
                                    @if($stock['maximum_stock'] > 0)
                                        <div class="text-sm text-gray-900">
                                            Max: {{ number_format($stock['maximum_stock'], 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Unit: {{ number_format($stock['unit_cost'], 2) }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">
                                        Total: {{ number_format($stock['total_value'], 2) }}
                                    </div>
                                    @if($stock['average_cost'] > 0 && $stock['average_cost'] != $stock['unit_cost'])
                                        <div class="text-sm text-blue-600">
                                            Avg: {{ number_format($stock['average_cost'], 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($stock['current_stock'] <= 0)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Out of Stock
                                        </span>
                                    @elseif($stock['current_stock'] <= $stock['reorder_level'])
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Low Stock
                                        </span>
                                    @elseif($stock['maximum_stock'] > 0 && $stock['current_stock'] >= $stock['maximum_stock'])
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                            Overstock
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            In Stock
                                        </span>
                                    @endif
                                    @if($stock['days_since_update'] > 30)
                                        <div class="text-xs text-red-600 mt-1">
                                            Stale ({{ $stock['days_since_update'] }}d)
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($stock['last_updated'])->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($stock['last_updated'])->format('h:i A') }}
                                    </div>
                                    @if($stock['last_movement_type'])
                                        <div class="text-xs text-blue-600">
                                            Last: {{ $stock['last_movement_type'] }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <i class="fas fa-inbox text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No stock records found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back later.</p>
        </div>
    @endif
</div>
@endsection
