@extends('layouts.admin')
@section('header-title', 'GRN Item Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg mb-6">
        <x-nav-buttons :items="[
            ['name' => 'GRN Master Reports', 'link' => route('admin.reports.inventory.grn.master')],
            ['name' => 'GRN Item Reports', 'link' => route('admin.reports.inventory.grn.item')],
        ]" active="GRN Item Reports" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">GRN Item Reports</h2>
            <div class="text-sm text-gray-500">
                {{ \Carbon\Carbon::now()->format('F d, Y') }}
            </div>
        </div>
        <p class="text-gray-600">
            Detailed item-wise analysis of Goods Receipt Notes including quantity tracking, pricing analysis, and item-specific discount calculations.
        </p>
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
                'name' => 'supplier_id',
                'label' => 'Supplier',
                'type' => 'select',
                'placeholder' => 'All Suppliers',
                'options' => isset($suppliers) ? $suppliers->pluck('name', 'id')->toArray() : []
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

    @if(isset($reportData) && !empty($reportData))
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Items</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $summary['total_items'] ?? 0 }}</p>
                </div>
                <i class="fas fa-boxes text-blue-500 text-2xl"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Quantity</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($summary['total_quantity'] ?? 0, 2) }}</p>
                </div>
                <i class="fas fa-cubes text-green-500 text-2xl"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Value</p>
                    <p class="text-2xl font-bold text-purple-600">${{ number_format($summary['total_value'] ?? 0, 2) }}</p>
                </div>
                <i class="fas fa-dollar-sign text-purple-500 text-2xl"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Discounts</p>
                    <p class="text-2xl font-bold text-orange-600">${{ number_format($summary['total_discounts'] ?? 0, 2) }}</p>
                </div>
                <i class="fas fa-percentage text-orange-500 text-2xl"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Average Unit Price</p>
                    <p class="text-2xl font-bold text-indigo-600">${{ number_format($summary['avg_unit_price'] ?? 0, 2) }}</p>
                </div>
                <i class="fas fa-calculator text-indigo-500 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">GRN Item Report Data</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($reportData as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                            <a href="{{ route('admin.reports.inventory.grn.view', $item['grn_master_id']) }}" class="hover:underline">
                                {{ $item['grn_number'] }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($item['date'])->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                @if($item['item_image'])
                                    <img src="{{ asset('storage/' . $item['item_image']) }}" alt="{{ $item['item_name'] }}" class="w-8 h-8 rounded-full mr-2">
                                @else
                                    <div class="w-8 h-8 bg-gray-200 rounded-full mr-2 flex items-center justify-center">
                                        <i class="fas fa-box text-gray-400 text-xs"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium">{{ $item['item_name'] }}</div>
                                    @if($item['item_code'])
                                        <div class="text-xs text-gray-500">{{ $item['item_code'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item['category_name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item['unit'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item['quantity'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${{ number_format($item['unit_price'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($item['discount_amount'] > 0)
                                <span class="text-orange-600">
                                    ${{ number_format($item['discount_amount'], 2) }}
                                    @if($item['discount_percentage'] > 0)
                                        ({{ number_format($item['discount_percentage'], 1) }}%)
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${{ number_format($item['net_amount'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item['supplier_name'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item['branch_name'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(isset($reportData) && is_array($reportData) && count($reportData) > 100)
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="text-sm text-gray-500">
                Showing {{ count($reportData) }} records. Consider applying more specific filters for better performance.
            </div>
        </div>
        @endif
    </div>

    <!-- Item Analysis Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <!-- Top Items by Quantity -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Items by Quantity</h3>
            <div class="space-y-3">
                @foreach(($topItemsByQuantity ?? []) as $topItem)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">{{ $topItem['item_name'] }}</div>
                        <div class="text-sm text-gray-500">{{ $topItem['category_name'] }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-medium text-gray-900">{{ number_format($topItem['total_quantity'], 2) }}</div>
                        <div class="text-sm text-gray-500">${{ number_format($topItem['total_value'], 2) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Top Items by Value -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Items by Value</h3>
            <div class="space-y-3">
                @foreach(($topItemsByValue ?? []) as $topItem)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">{{ $topItem['item_name'] }}</div>
                        <div class="text-sm text-gray-500">{{ $topItem['category_name'] }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-medium text-gray-900">${{ number_format($topItem['total_value'], 2) }}</div>
                        <div class="text-sm text-gray-500">{{ number_format($topItem['total_quantity'], 2) }} {{ $topItem['unit'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <!-- No Data Message -->
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No GRN item data found</h3>
        <p class="text-gray-500 mb-6">
            No GRN item records match your current filter criteria. Try adjusting your filters or check if there are any GRN items in the system.
        </p>
        <a href="{{ route('admin.reports.inventory.grn.item') }}"
           class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
            Reset Filters
        </a>
    </div>
    @endif
</div>
@endsection
