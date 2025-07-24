
<!--- Unused Remove -->
{{-- 
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

                <!-- PDF Export Dropdown -->
                <div class="relative inline-block text-left">
                    <button type="button" class="inline-flex justify-center w-full rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" id="pdf-export-menu" onclick="togglePdfMenu()">
                        <i class="fas fa-file-pdf mr-2"></i>
                        PDF Options
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>

                    <div class="hidden origin-top-right absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" id="pdf-menu">
                        <div class="py-1" role="menu" aria-orientation="vertical">
                            <!-- Detailed View Options -->
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b">Detailed View</div>
                            <div class="flex">
                                <a href="{{ route('admin.reports.inventory.stock', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'detailed', 'preview' => true])) }}" target="_blank" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2 text-blue-500"></i>
                                    Preview
                                </a>
                                <a href="{{ route('admin.reports.inventory.stock', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'detailed'])) }}" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-l">
                                    <i class="fas fa-download mr-2 text-green-500"></i>
                                    Download
                                </a>
                            </div>

                            <!-- Summary View Options -->
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-t">Summary View</div>
                            <div class="flex">
                                <a href="{{ route('admin.reports.inventory.stock', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'summary', 'preview' => true])) }}" target="_blank" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2 text-blue-500"></i>
                                    Preview
                                </a>
                                <a href="{{ route('admin.reports.inventory.stock', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'summary'])) }}" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-l">
                                    <i class="fas fa-download mr-2 text-green-500"></i>
                                    Download
                                </a>
                            </div>

                            <!-- Master Only View Options -->
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-t">Master Only</div>
                            <div class="flex">
                                <a href="{{ route('admin.reports.inventory.stock', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'master_only', 'preview' => true])) }}" target="_blank" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2 text-blue-500"></i>
                                    Preview
                                </a>
                                <a href="{{ route('admin.reports.inventory.stock', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'master_only'])) }}" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-l">
                                    <i class="fas fa-download mr-2 text-green-500"></i>
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function togglePdfMenu() {
                    const menu = document.getElementById('pdf-menu');
                    menu.classList.toggle('hidden');

                    // Close menu when clicking outside
                    document.addEventListener('click', function closeMenu(e) {
                        if (!document.getElementById('pdf-export-menu').contains(e.target)) {
                            menu.classList.add('hidden');
                            document.removeEventListener('click', closeMenu);
                        }
                    });
                }
                </script>
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
                'options' => isset($items) ? $items->mapWithKeys(function($item) {
                    return [$item->id => $item->name . ' (' . $item->item_code . ')'];
                })->toArray() : []
            ],
            [
                'name' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'placeholder' => 'All Categories',
                'options' => isset($categories) ? $categories->pluck('name', 'id')->toArray() : []
            ],
            [
                'name' => 'transaction_type',
                'label' => 'Transaction Type',
                'type' => 'select',
                'placeholder' => 'All Types',
                'options' => [
                    'sales_order' => 'Sales Orders',
                    'production_issue' => 'Production Issues',
                    'production_in' => 'Production Receipts',
                    'waste' => 'Wastage',
                    'gtn_outgoing' => 'GTN Outgoing',
                    'gtn_incoming' => 'GTN Incoming',
                    'grn_stock_in' => 'GRN Stock In'
                ]
            ]
        ]"
        showDateRange="true"
        showBranchFilter="true"
    />

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
