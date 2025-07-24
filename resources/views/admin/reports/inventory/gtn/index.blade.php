
<!--- Unused Remove -->
{{-- 
@extends('layouts.admin')
@section('header-title', 'GTN Reports')

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
        ]" active="Goods Transfer Note Report" />
    </div>

    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Goods Transfer Note (GTN) Report</h1>
                <p class="text-gray-600">Track inter-branch transfer statuses and stock movements</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.inventory.gtn', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel (Multi-Sheet)
                </a>

                {{-- <a href="{{ route('admin.exports.multisheet', ['reportType' => 'goods_transfer_note_master']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                     class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel 2 (Multi-Sheet)
                </a> --}}

                <a href="{{ route('admin.reports.inventory.gtn', array_merge(request()->query(), ['export' => 'csv'])) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-file-csv mr-2"></i> Export CSV
                </a>

                <!-- PDF Export Dropdown -->
                <div class="relative inline-block text-left">
                    <button type="button" class="inline-flex justify-center w-full rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" id="pdf-export-menu-gtn" onclick="togglePdfMenuGTN()">
                        <i class="fas fa-file-pdf mr-2"></i>
                        PDF Options
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>

                    <div class="hidden origin-top-right absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" id="pdf-menu-gtn">
                        <div class="py-1" role="menu" aria-orientation="vertical">
                            <!-- Detailed View Options -->
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b">Detailed View</div>
                            <div class="flex">
                                <a href="{{ route('admin.reports.inventory.gtn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'detailed', 'preview' => true])) }}" target="_blank" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2 text-blue-500"></i>
                                    Preview
                                </a>
                                <a href="{{ route('admin.reports.inventory.gtn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'detailed'])) }}" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-l">
                                    <i class="fas fa-download mr-2 text-green-500"></i>
                                    Download
                                </a>
                            </div>

                            <!-- Summary View Options -->
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-t">Summary View</div>
                            <div class="flex">
                                <a href="{{ route('admin.reports.inventory.gtn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'summary', 'preview' => true])) }}" target="_blank" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2 text-blue-500"></i>
                                    Preview
                                </a>
                                <a href="{{ route('admin.reports.inventory.gtn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'summary'])) }}" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-l">
                                    <i class="fas fa-download mr-2 text-green-500"></i>
                                    Download
                                </a>
                            </div>

                            <!-- Master Only View Options -->
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-t">Master Only</div>
                            <div class="flex">
                                <a href="{{ route('admin.reports.inventory.gtn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'master_only', 'preview' => true])) }}" target="_blank" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2 text-blue-500"></i>
                                    Preview
                                </a>
                                <a href="{{ route('admin.reports.inventory.gtn', array_merge(request()->query(), ['export' => 'pdf', 'view_type' => 'master_only'])) }}" class="flex-1 flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-l">
                                    <i class="fas fa-download mr-2 text-green-500"></i>
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function togglePdfMenuGTN() {
                    const menu = document.getElementById('pdf-menu-gtn');
                    menu.classList.toggle('hidden');

                    // Close menu when clicking outside
                    document.addEventListener('click', function closeMenu(e) {
                        if (!document.getElementById('pdf-export-menu-gtn').contains(e.target)) {
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
        :customFilters="[
            [
                'name' => 'origin_status',
                'label' => 'Origin Status',
                'type' => 'select',
                'placeholder' => 'All Statuses',
                'options' => [
                    'draft' => 'Draft',
                    'confirmed' => 'Confirmed',
                    'in_delivery' => 'In Delivery',
                    'delivered' => 'Delivered'
                ]
            ],
            [
                'name' => 'receiver_status',
                'label' => 'Receiver Status',
                'type' => 'select',
                'placeholder' => 'All Statuses',
                'options' => [
                    'pending' => 'Pending',
                    'received' => 'Received',
                    'verified' => 'Verified',
                    'accepted' => 'Accepted',
                    'rejected' => 'Rejected',
                    'partially_accepted' => 'Partially Accepted'
                ]
            ],
            [
                'name' => 'from_branch_id',
                'label' => 'From Branch',
                'type' => 'select',
                'placeholder' => 'All Branches',
                'options' => isset($branches) ? $branches->pluck('name', 'id')->toArray() : []
            ],
            [
                'name' => 'to_branch_id',
                'label' => 'To Branch',
                'type' => 'select',
                'placeholder' => 'All Branches',
                'options' => isset($branches) ? $branches->pluck('name', 'id')->toArray() : []
            ]
        ]"
        showDateRange="true"
    />

    <!-- Report Results -->
    @if(isset($reportData) && $reportData['gtns']->count() > 0)
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
                        <p class="text-sm font-medium text-gray-500">Total GTNs</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $reportData['summary']['total_gtns'] }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Total Transfer Value</p>
                        <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($reportData['summary']['total_transfer_value'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Accepted Value</p>
                        <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($reportData['summary']['total_accepted_value'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-percentage text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Acceptance Rate</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($reportData['summary']['overall_acceptance_rate'], 1) }}%</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GTN Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From → To</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Origin Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Receiver Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer Value</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Accepted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acceptance Rate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reportData['gtns'] as $gtn)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $gtn['gtn_number'] }}</div>
                                        <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($gtn['transfer_date'])->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="font-medium">{{ $gtn['from_branch'] }}</span>
                                        <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                                        <span class="font-medium">{{ $gtn['to_branch'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">{{ $gtn['items_count'] }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($gtn['total_quantity'], 0) }} units</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $originColors = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'confirmed' => 'bg-blue-100 text-blue-800',
                                            'in_delivery' => 'bg-yellow-100 text-yellow-800',
                                            'delivered' => 'bg-green-100 text-green-800'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $originColors[$gtn['origin_status']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $gtn['origin_status'])) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $receiverColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'received' => 'bg-blue-100 text-blue-800',
                                            'verified' => 'bg-purple-100 text-purple-800',
                                            'accepted' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'partially_accepted' => 'bg-orange-100 text-orange-800'
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $receiverColors[$gtn['receiver_status']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $gtn['receiver_status'])) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    Rs. {{ number_format($gtn['total_transfer_value'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                                    {{ number_format($gtn['accepted_quantity'], 0) }}
                                    <div class="text-xs text-gray-500">Rs. {{ number_format($gtn['total_accepted_value'], 0) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                    {{ number_format($gtn['rejected_quantity'], 0) }}
                                    <div class="text-xs text-gray-500">Rs. {{ number_format($gtn['total_rejected_value'], 0) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $acceptanceRate = $gtn['acceptance_rate'];
                                        $rateClass = $acceptanceRate >= 90 ? 'bg-green-100 text-green-800' :
                                                    ($acceptanceRate >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $rateClass }}">
                                        {{ number_format($acceptanceRate, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Analysis -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Status Distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">GTN Status Distribution</h3>
                @php
                    $statusCounts = $reportData['gtns']->groupBy('receiver_status')->map->count();
                @endphp
                <div class="space-y-3">
                    @foreach($statusCounts as $status => $count)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 mr-2">{{ $count }}</span>
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $reportData['summary']['total_gtns'] > 0 ? ($count / $reportData['summary']['total_gtns']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">High Acceptance Rate</div>
                            <div class="text-xs text-gray-500">≥90% acceptance</div>
                        </div>
                        <div class="text-lg font-semibold text-green-600">
                            {{ $reportData['gtns']->where('acceptance_rate', '>=', 90)->count() }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Moderate Acceptance</div>
                            <div class="text-xs text-gray-500">70-89% acceptance</div>
                        </div>
                        <div class="text-lg font-semibold text-yellow-600">
                            {{ $reportData['gtns']->whereBetween('acceptance_rate', [70, 89])->count() }}
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-900">Low Acceptance</div>
                            <div class="text-xs text-gray-500">&lt;70% acceptance</div>
                        </div>
                        <div class="text-lg font-semibold text-red-600">
                            {{ $reportData['gtns']->where('acceptance_rate', '<', 70)->count() }}
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">No GTNs Found</h3>
            <p class="text-gray-500">No Goods Transfer Notes found for the selected criteria. Try adjusting your filters.</p>
        </div>
    @endif
</div>
@endsection
