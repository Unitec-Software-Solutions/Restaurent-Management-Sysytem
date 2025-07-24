@extends('layouts.admin')
@section('header-title', 'Stock Movement Report')

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
        ]" active="Stock Movement" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Stock Movement Report</h1>
                <p class="text-gray-600">Track all inventory movements including purchases, sales, transfers, and adjustments</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.stock.movement', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>
                <a href="{{ route('admin.reports.inventory.stock.movement', array_merge(request()->query(), ['export' => 'pdf'])) }}"
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
                'name' => 'item_id',
                'label' => 'Item',
                'type' => 'select',
                'placeholder' => 'All Items',
                'options' => isset($items) ? $items->pluck('name', 'id')->toArray() : []
            ],
            [
                'name' => 'movement_type',
                'label' => 'Movement Type',
                'type' => 'select',
                'placeholder' => 'All Types',
                'options' => [
                    'purchase' => 'Purchase',
                    'sale' => 'Sale',
                    'transfer_in' => 'Transfer In',
                    'transfer_out' => 'Transfer Out',
                    'adjustment' => 'Adjustment',
                    'damage' => 'Damage',
                    'wastage' => 'Wastage'
                ]
            ],
            [
                'name' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'placeholder' => 'All Categories',
                'options' => isset($categories) ? $categories->pluck('name', 'id')->toArray() : []
            ]
        ]"
        showDateRange="true"
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
                            <i class="fas fa-exchange-alt text-indigo-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Movements</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_movements'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-arrow-up text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Stock In</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_in_qty']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-arrow-down text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Stock Out</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_out_qty']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-balance-scale text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Net Movement</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['net_movement']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Value</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_value'], 2) }}</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Movement Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost & Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['data'] as $movement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($movement['transaction_date'])->format('M d, Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($movement['transaction_date'])->format('h:i A') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $movement['item_name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $movement['item_code'] ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-400">{{ $movement['category'] ?? 'Uncategorized' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($movement['movement_type'] == 'purchase') bg-blue-100 text-blue-800
                                        @elseif($movement['movement_type'] == 'sale') bg-green-100 text-green-800
                                        @elseif(in_array($movement['movement_type'], ['transfer_in', 'transfer_out'])) bg-purple-100 text-purple-800
                                        @elseif($movement['movement_type'] == 'adjustment') bg-yellow-100 text-yellow-800
                                        @elseif(in_array($movement['movement_type'], ['damage', 'wastage'])) bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucwords(str_replace('_', ' ', $movement['movement_type'])) }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $movement['movement_direction'] == 'in' ? 'Stock In' : 'Stock Out' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $movement['branch_name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $movement['branch_code'] ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium
                                        @if($movement['movement_direction'] == 'in') text-green-600
                                        @else text-red-600 @endif">
                                        {{ $movement['movement_direction'] == 'in' ? '+' : '-' }}{{ number_format($movement['quantity'], 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $movement['unit'] ?? 'units' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Before: {{ number_format($movement['stock_before'], 2) }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">
                                        After: {{ number_format($movement['stock_after'], 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        Unit: {{ number_format($movement['unit_cost'], 2) }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">
                                        Total: {{ number_format($movement['total_cost'], 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($movement['reference_number'])
                                        <div class="text-sm text-gray-900">{{ $movement['reference_number'] }}</div>
                                    @endif
                                    @if($movement['reference_type'])
                                        <div class="text-xs text-gray-500">{{ $movement['reference_type'] }}</div>
                                    @endif
                                    @if($movement['notes'])
                                        <div class="text-xs text-gray-400 mt-1">
                                            {{ Str::limit($movement['notes'], 30) }}
                                        </div>
                                    @endif
                                    <div class="text-xs text-gray-500 mt-1">
                                        By: {{ $movement['created_by'] }}
                                    </div>
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">No stock movement records found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back later.</p>
        </div>
    @endif
</div>
@endsection
