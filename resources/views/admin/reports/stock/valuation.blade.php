@extends('layouts.admin')
@section('header-title', 'Stock Valuation Report')

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
        ]" active="Stock Valuation" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Stock Valuation Report</h1>
                <p class="text-gray-600">Comprehensive inventory valuation analysis with cost methods and aging analysis</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.stock.valuation', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>
                <a href="{{ route('admin.reports.inventory.stock.valuation', array_merge(request()->query(), ['export' => 'pdf'])) }}"
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
                'name' => 'valuation_date',
                'label' => 'Valuation Date',
                'type' => 'date'
            ],
            [
                'name' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'placeholder' => 'All Categories',
                'options' => isset($categories) ? $categories->pluck('name', 'id')->toArray() : []
            ],
            [
                'name' => 'valuation_method',
                'label' => 'Valuation Method',
                'type' => 'select',
                'options' => [
                    'fifo' => 'FIFO',
                    'lifo' => 'LIFO',
                    'average' => 'Weighted Average',
                    'current' => 'Current Cost'
                ]
            ],
            [
                'name' => 'include_zero_stock',
                'label' => 'Include Zero Stock',
                'type' => 'select',
                'options' => [
                    '0' => 'No',
                    '1' => 'Yes'
                ]
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
                        <p class="text-sm font-medium text-gray-500">Total Quantity</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_quantity']) }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Total Value</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['total_value'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calculator text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Avg Unit Value</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['average_unit_value'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-pie text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Top Category</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['top_category'] }}</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aging Analysis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Movement</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['data'] as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item['item_name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $item['item_code'] ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-400">{{ $item['category'] ?? 'Uncategorized' }}</div>
                                    <div class="text-xs text-gray-400">Unit: {{ $item['unit'] ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item['branch_name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $item['branch_code'] ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-semibold text-gray-900">
                                        {{ number_format($item['current_stock'], 2) }}
                                    </div>
                                    @if($item['reserved_stock'] > 0)
                                        <div class="text-xs text-blue-600">
                                            Reserved: {{ number_format($item['reserved_stock'], 2) }}
                                        </div>
                                    @endif
                                    @if($item['available_stock'] != $item['current_stock'])
                                        <div class="text-xs text-green-600">
                                            Available: {{ number_format($item['available_stock'], 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ number_format($item['unit_cost'], 2) }}
                                    </div>
                                    @if($item['average_cost'] > 0 && $item['average_cost'] != $item['unit_cost'])
                                        <div class="text-sm text-blue-600">
                                            Avg: {{ number_format($item['average_cost'], 2) }}
                                        </div>
                                    @endif
                                    @if($item['last_purchase_cost'] > 0 && $item['last_purchase_cost'] != $item['unit_cost'])
                                        <div class="text-sm text-green-600">
                                            Last: {{ number_format($item['last_purchase_cost'], 2) }}
                                        </div>
                                    @endif
                                    <div class="text-xs text-gray-500">{{ ucfirst($item['valuation_method']) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-semibold text-gray-900">
                                        {{ number_format($item['total_value'], 2) }}
                                    </div>
                                    @if($item['unrealized_gain_loss'] != 0)
                                        <div class="text-sm {{ $item['unrealized_gain_loss'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $item['unrealized_gain_loss'] > 0 ? '+' : '' }}{{ number_format($item['unrealized_gain_loss'], 2) }}
                                        </div>
                                    @endif
                                    <div class="text-xs text-gray-500">
                                        {{ number_format($item['percentage_of_total'], 1) }}% of total
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item['aging_days'] > 0)
                                        <div class="text-sm text-gray-900">
                                            {{ $item['aging_days'] }} days
                                        </div>
                                        @if($item['aging_days'] > 365)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Very Old
                                            </span>
                                        @elseif($item['aging_days'] > 180)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                                Old
                                            </span>
                                        @elseif($item['aging_days'] > 90)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Aging
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Fresh
                                            </span>
                                        @endif
                                        @if($item['turnover_ratio'] > 0)
                                            <div class="text-xs text-blue-600 mt-1">
                                                Turnover: {{ number_format($item['turnover_ratio'], 1) }}x
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-sm text-gray-500">N/A</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item['last_movement_date'])
                                        <div class="text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($item['last_movement_date'])->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $item['last_movement_type'] ?? 'Unknown' }}
                                        </div>
                                        @if($item['days_since_last_movement'] > 30)
                                            <div class="text-xs text-red-600">
                                                {{ $item['days_since_last_movement'] }} days ago
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-sm text-gray-500">No movements</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Method Explanation -->
        <div class="bg-blue-50 rounded-xl p-6 mt-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">Valuation Method: {{ ucfirst($valuationMethod) }}</h3>
            <div class="text-sm text-blue-800">
                @if($valuationMethod == 'fifo')
                    <p><strong>First In, First Out (FIFO):</strong> Items are valued based on the cost of the oldest inventory first. This method assumes that items purchased first are sold first.</p>
                @elseif($valuationMethod == 'lifo')
                    <p><strong>Last In, First Out (LIFO):</strong> Items are valued based on the cost of the newest inventory first. This method assumes that items purchased last are sold first.</p>
                @elseif($valuationMethod == 'average')
                    <p><strong>Weighted Average:</strong> Items are valued using the weighted average cost of all inventory purchases. This provides a balanced approach between FIFO and LIFO.</p>
                @else
                    <p><strong>Current Cost:</strong> Items are valued using the most recent purchase cost or current market value. This reflects the current replacement cost.</p>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <i class="fas fa-inbox text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No stock valuation data found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back later.</p>
        </div>
    @endif
</div>
@endsection
