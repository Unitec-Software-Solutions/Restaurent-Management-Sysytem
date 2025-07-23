
@extends('layouts.admin')
@section('header-title', 'Stock Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg">
        <x-nav-buttons :items="[
            ['name' => 'Inventory Report', 'link' => route('admin.reports.inventory.index')],
            ['name' => 'Stock Report', 'link' => route('admin.reports.inventory.stock')],
            ['name' => 'Category Report', 'link' => route('admin.reports.inventory.category')],
            ['name' => 'Goods Transfer Note Report', 'link' => route('admin.reports.inventory.gtn')],
            ['name' => 'Goods Receipt Note Report', 'link' => route('admin.reports.inventory.grn')],
            ['name' => 'Stock Release Note Report', 'link' => route('admin.reports.inventory.srn')],
        ]" active="Stock Report" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Inventory Stock Report</h1>
                <p class="text-gray-600">Detailed stock analysis per item with transaction breakdowns</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.stock', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel (Multi-Sheet)
                </a>
                <a href="{{ route('admin.reports.inventory.stock', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-csv mr-2"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.reports.inventory.stock') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Item Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                    <select name="item_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Items</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ $itemId == $item->id ? 'selected' : '' }}>
                                {{ $item->name }} ({{ $item->item_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Branch Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Transaction Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                    <select name="transaction_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="sales_order" {{ $transactionType == 'sales_order' ? 'selected' : '' }}>Sales Orders</option>
                        <option value="production_issue" {{ $transactionType == 'production_issue' ? 'selected' : '' }}>Production Issues</option>
                        <option value="production_in" {{ $transactionType == 'production_in' ? 'selected' : '' }}>Production Receipts</option>
                        <option value="waste" {{ $transactionType == 'waste' ? 'selected' : '' }}>Wastage</option>
                        <option value="gtn_outgoing" {{ $transactionType == 'gtn_outgoing' ? 'selected' : '' }}>GTN Outgoing</option>
                        <option value="gtn_incoming" {{ $transactionType == 'gtn_incoming' ? 'selected' : '' }}>GTN Incoming</option>
                        <option value="grn_stock_in" {{ $transactionType == 'grn_stock_in' ? 'selected' : '' }}>GRN Stock In</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search mr-2"></i> Generate Report
                </button>
                <a href="{{ route('admin.reports.inventory.stock') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-undo mr-2"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Report Results -->
    @if($reportData->count() > 0)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-boxes text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Items</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData->count() }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Total Stock In</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData->sum('stock_in'), 2) }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Total Stock Out</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData->sum('stock_out'), 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-warehouse text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Current Stock</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData->sum('current_stock'), 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Opening</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock In</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Out</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Current</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Production</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Wastage</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item['item_name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $item['item_code'] }} • {{ $item['category_name'] }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item['branch_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($item['opening_stock'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                                    {{ number_format($item['stock_in'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                    {{ number_format($item['stock_out'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                    {{ number_format($item['current_stock'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($item['sales_quantity'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    <div class="text-green-600">+{{ number_format($item['production_received'], 2) }}</div>
                                    <div class="text-red-600">-{{ number_format($item['production_used'], 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                    {{ number_format($item['wastage'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $statusColors = [
                                            'in_stock' => 'bg-green-100 text-green-800',
                                            'low_stock' => 'bg-yellow-100 text-yellow-800',
                                            'out_of_stock' => 'bg-red-100 text-red-800'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$item['stock_status']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $item['stock_status'])) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transaction Summary -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Usage Items</h3>
                @php
                    $topSelling = $reportData->sortByDesc('sales_quantity')->take(5);
                @endphp
                <div class="space-y-3">
                    @foreach($topSelling as $item)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">{{ $item['item_name'] }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ number_format($item['sales_quantity'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">High Usage Items</h3>
                @php
                    $highWastage = $reportData->sortByDesc('wastage')->take(5);
                @endphp
                <div class="space-y-3">
                    @foreach($highWastage as $item)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">{{ $item['item_name'] }}</span>
                            <span class="text-sm font-medium text-red-600">{{ number_format($item['wastage'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Low Stock Alerts</h3>
                @php
                    $lowStock = $reportData->where('stock_status', 'low_stock')->take(5);
                @endphp
                <div class="space-y-3">
                    @foreach($lowStock as $item)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">{{ $item['item_name'] }}</span>
                            <span class="text-sm font-medium text-yellow-600">{{ number_format($item['current_stock'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-chart-bar text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Data Found</h3>
            <p class="text-gray-500">No stock data found for the selected criteria. Try adjusting your filters.</p>
        </div>
    @endif
</div>
@endsection
