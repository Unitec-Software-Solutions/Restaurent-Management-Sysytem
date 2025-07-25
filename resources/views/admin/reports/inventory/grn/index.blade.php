@extends('layouts.admin')
@section('header-title', 'GRN Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Navigation Buttons -->
    <div class="rounded-lg">
        <x-nav-buttons :items="[
            ['name' => 'Inventory Report', 'link' => route('admin.reports.inventory.index')],
            ['name' => 'Stock Report', 'link' => route('admin.reports.inventory.stock')],
            ['name' => 'Goods Transfer Note Report', 'link' => route('admin.reports.inventory.gtn')],
            ['name' => 'Goods Receipt Note Report', 'link' => route('admin.reports.inventory.grn')],
            ['name' => 'Stock Release Note Report', 'link' => route('admin.reports.inventory.srn')],
            ['name' => 'Category Report', 'link' => route('admin.reports.inventory.category')],
        ]" active="Goods Receipt Note Report" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Goods Receipt Note (GRN) Report</h1>
                <p class="text-gray-600">Track purchasing activities, payment statuses, and supplier performance</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.grn', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel (Multi-Sheet)
                </a>
                <a href="{{ route('admin.reports.inventory.grn', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-csv mr-2"></i> Export CSV
                </a>

                <!-- PDF Export Dropdown -->
                <div class="relative inline-block text-left">
                    <button type="button" class="inline-flex justify-center w-full rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" id="pdf-export-menu-grn" onclick="togglePdfMenuGRN()">
                        <i class="fas fa-file-pdf mr-2"></i>
                        PDF Options
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>

                    <div class="hidden origin-top-right absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" id="pdf-menu-grn">
                        <div class="py-1" role="menu" aria-orientation="vertical">
                            <!-- Detailed View Options -->
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b">Detailed View</div>
                            <div class="flex">
                                <a href="{{ route('admin.reports.inventory.grn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'detailed', 'preview' => true])) }}" target="_blank" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2 text-blue-500"></i>
                                    Preview
                                </a>
                                <a href="{{ route('admin.reports.inventory.grn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'detailed'])) }}" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-l">
                                    <i class="fas fa-download mr-2 text-green-500"></i>
                                    Download
                                </a>
                            </div>

                            <!-- Summary View Options -->
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-t">Summary View</div>
                            <div class="flex">
                                <a href="{{ route('admin.reports.inventory.grn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'summary', 'preview' => true])) }}" target="_blank" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2 text-blue-500"></i>
                                    Preview
                                </a>
                                <a href="{{ route('admin.reports.inventory.grn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'summary'])) }}" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-l">
                                    <i class="fas fa-download mr-2 text-green-500"></i>
                                    Download
                                </a>
                            </div>

                            <!-- Master Only View Options -->
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-t">Supplier Details</div>
                            <div class="flex">
                                <a href="{{ route('admin.reports.inventory.grn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'supplier_details', 'preview' => true])) }}" target="_blank" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2 text-blue-500"></i>
                                    Preview
                                </a>
                                <a href="{{ route('admin.reports.inventory.grn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'supplier_details'])) }}" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-l">
                                    <i class="fas fa-download mr-2 text-green-500"></i>
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function togglePdfMenuGRN() {
                    const menu = document.getElementById('pdf-menu-grn');
                    menu.classList.toggle('hidden');

                    // Close menu when clicking outside
                    document.addEventListener('click', function closeMenu(e) {
                        if (!document.getElementById('pdf-export-menu-grn').contains(e.target)) {
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
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.reports.inventory.grn') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom ?? date('Y-m-01') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ $dateTo ?? date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ ($status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="verified" {{ ($status ?? '') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="rejected" {{ ($status ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Payment Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                    <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Payment Statuses</option>
                        <option value="unpaid" {{ ($paymentStatus ?? '') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ ($paymentStatus ?? '') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ ($paymentStatus ?? '') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <!-- Supplier Filter -->
                @if(isset($suppliers) && $suppliers->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ ($supplierId ?? '') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Branch Filter -->
                @if(isset($branches) && $branches->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ ($branchId ?? '') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search mr-2"></i> Generate Report
                </button>
                <a href="{{ route('admin.reports.inventory.grn') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-undo mr-2"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Report Results -->
    @if(isset($reportData) && $reportData['grns']->count() > 0)
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total GRNs</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_grns'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Purchase Value</p>
                        <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($reportData['summary']['total_purchase_value'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Paid Amount</p>
                        <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($reportData['summary']['total_paid'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Outstanding</p>
                        <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($reportData['summary']['total_outstanding'] ?? 0, 2) }}</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Value</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['grns'] as $grn)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $grn['grn_number'] ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ isset($grn['received_date']) ? \Carbon\Carbon::parse($grn['received_date'])->format('M d, Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $grn['supplier_name'] ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $grn['branch_name'] ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">{{ $grn['items_count'] ?? 0 }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'verified' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800'
                                        ];
                                        $status = $grn['status'] ?? 'pending';
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $paymentColors = [
                                            'unpaid' => 'bg-red-100 text-red-800',
                                            'partial' => 'bg-yellow-100 text-yellow-800',
                                            'paid' => 'bg-green-100 text-green-800'
                                        ];
                                        $paymentStatus = $grn['payment_status'] ?? 'unpaid';
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $paymentColors[$paymentStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($paymentStatus) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    Rs. {{ number_format($grn['total_purchase_value'] ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                    Rs. {{ number_format($grn['outstanding_amount'] ?? 0, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Analysis -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Supplier Performance -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Suppliers by Volume</h3>
                @php
                    $topSuppliers = $reportData['grns']->groupBy('supplier_name')
                        ->map(function ($group) {
                            return [
                                'name' => $group->first()['supplier_name'],
                                'total_value' => $group->sum('total_purchase_value'),
                                'grn_count' => $group->count()
                            ];
                        })
                        ->sortByDesc('total_value')
                        ->take(5);
                @endphp
                <div class="space-y-3">
                    @foreach($topSuppliers as $supplier)
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $supplier['name'] }}</span>
                                <span class="text-xs text-gray-500 ml-2">({{ $supplier['grn_count'] }} GRNs)</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">Rs. {{ number_format($supplier['total_value'], 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Payment Analysis -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Status Overview</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Fully Paid</div>
                            <div class="text-xs text-gray-500">{{ $reportData['grns']->where('payment_status', 'paid')->count() }} GRNs</div>
                        </div>
                        <div class="text-lg font-semibold text-green-600">
                            Rs. {{ number_format($reportData['grns']->where('payment_status', 'paid')->sum('total_purchase_value'), 0) }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Partially Paid</div>
                            <div class="text-xs text-gray-500">{{ $reportData['grns']->where('payment_status', 'partial')->count() }} GRNs</div>
                        </div>
                        <div class="text-lg font-semibold text-yellow-600">
                            Rs. {{ number_format($reportData['grns']->where('payment_status', 'partial')->sum('outstanding_amount'), 0) }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Unpaid</div>
                            <div class="text-xs text-gray-500">{{ $reportData['grns']->where('payment_status', 'unpaid')->count() }} GRNs</div>
                        </div>
                        <div class="text-lg font-semibold text-red-600">
                            Rs. {{ number_format($reportData['grns']->where('payment_status', 'unpaid')->sum('outstanding_amount'), 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No GRNs Found</h3>
            <p class="text-gray-500">No Goods Receipt Notes found for the selected criteria. Try adjusting your filters.</p>
        </div>
    @endif
</div>
@endsection
