@extends('admin.reports.pdf.base')

@section('content')
<!-- Filters Applied -->
@if(!empty($filters))
<div class="filters-section">
    <div class="filters-title">Applied Filters</div>
    <div class="filters-grid">
        @if(isset($filters['date_from']) && $filters['date_from'])
            <div class="filter-item">
                <span class="filter-label">From Date:</span> {{ $filters['date_from'] }}
            </div>
        @endif
        @if(isset($filters['date_to']) && $filters['date_to'])
            <div class="filter-item">
                <span class="filter-label">To Date:</span> {{ $filters['date_to'] }}
            </div>
        @endif
        @if(isset($filters['status']) && $filters['status'])
            <div class="filter-item">
                <span class="filter-label">Status:</span> {{ ucfirst($filters['status']) }}
            </div>
        @endif
        @if(isset($filters['supplier_name']) && $filters['supplier_name'])
            <div class="filter-item">
                <span class="filter-label">Supplier:</span> {{ $filters['supplier_name'] }}
            </div>
        @endif
        @if(isset($filters['branch_name']) && $filters['branch_name'])
            <div class="filter-item">
                <span class="filter-label">Branch:</span> {{ $filters['branch_name'] }}
            </div>
        @endif
    </div>
</div>
@endif

<!-- Summary Statistics -->
@php
    $totalGrns = is_countable($grns) ? $grns->count() : count($grns);
    $pendingGrns = collect($grns)->where('status', 'pending')->count();
    $receivedGrns = collect($grns)->where('status', 'received')->count();
    $verifiedGrns = collect($grns)->where('status', 'verified')->count();
    $totalValue = collect($grns)->sum('total_purchase_value');
    $paidAmount = collect($grns)->sum('paid_amount');
    $outstandingAmount = collect($grns)->sum('outstanding_amount');
@endphp

<div class="summary-section">
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-value">{{ number_format($totalGrns) }}</div>
            <div class="summary-label">Total GRNs</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-warning">{{ number_format($pendingGrns) }}</div>
            <div class="summary-label">Pending</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-primary">{{ number_format($receivedGrns) }}</div>
            <div class="summary-label">Received</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-success">{{ number_format($verifiedGrns) }}</div>
            <div class="summary-label">Verified</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">Rs. {{ number_format($totalValue, 2) }}</div>
            <div class="summary-label">Total Value</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-success">Rs. {{ number_format($paidAmount, 2) }}</div>
            <div class="summary-label">Paid</div>
        </div>
        <div class="summary-card">
            <div class="summary-value text-danger">Rs. {{ number_format($outstandingAmount, 2) }}</div>
            <div class="summary-label">Outstanding</div>
        </div>
    </div>
</div>

@if($viewType === 'summary')
    <!-- Summary View - Supplier-wise Analysis -->
    <div class="avoid-break">
        <h3 class="mb-2" style="font-size: 14px; font-weight: bold; color: #374151;">Supplier-wise Summary</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%">Supplier</th>
                    <th style="width: 10%">GRNs</th>
                    <th style="width: 15%">Total Value</th>
                    <th style="width: 15%">Paid Amount</th>
                    <th style="width: 15%">Outstanding</th>
                    <th style="width: 10%">Avg Value</th>
                    <th style="width: 10%">Payment %</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $supplierStats = collect($grns)->groupBy('supplier_name')->map(function($items, $supplier) {
                        $totalValue = $items->sum('total_purchase_value');
                        $paidAmount = $items->sum('paid_amount');
                        $outstanding = $items->sum('outstanding_amount');
                        $paymentPercent = $totalValue > 0 ? ($paidAmount / $totalValue) * 100 : 0;

                        return [
                            'supplier' => $supplier ?: 'Unknown',
                            'count' => $items->count(),
                            'total_value' => $totalValue,
                            'paid_amount' => $paidAmount,
                            'outstanding' => $outstanding,
                            'avg_value' => $items->avg('total_purchase_value'),
                            'payment_percent' => $paymentPercent
                        ];
                    })->sortByDesc('total_value');
                @endphp
                @foreach($supplierStats as $stat)
                <tr>
                    <td class="font-semibold">{{ $stat['supplier'] }}</td>
                    <td class="text-center">{{ number_format($stat['count']) }}</td>
                    <td class="text-right">Rs. {{ number_format($stat['total_value'], 2) }}</td>
                    <td class="text-right text-success">Rs. {{ number_format($stat['paid_amount'], 2) }}</td>
                    <td class="text-right text-danger">Rs. {{ number_format($stat['outstanding'], 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($stat['avg_value'], 2) }}</td>
                    <td class="text-center">
                        <span class="@if($stat['payment_percent'] >= 80) text-success @elseif($stat['payment_percent'] >= 50) text-warning @else text-danger @endif">
                            {{ number_format($stat['payment_percent'], 1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif($viewType === 'master_only')
    <!-- Master Only View - Basic GRN Information -->
    <div class="avoid-break">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 6%">#</th>
                    <th style="width: 15%">GRN Number</th>
                    <th style="width: 12%">Date</th>
                    <th style="width: 20%">Supplier</th>
                    <th style="width: 15%">Branch</th>
                    <th style="width: 12%">Total Amount</th>
                    <th style="width: 10%">Status</th>
                    <th style="width: 10%">Payment Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grns as $index => $grn)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $grn['grn_number'] ?? 'N/A' }}</td>
                    <td class="text-sm">{{ isset($grn['received_date']) ? \Carbon\Carbon::parse($grn['received_date'])->format('M j, Y') : 'N/A' }}</td>
                    <td>{{ $grn['supplier_name'] ?? 'N/A' }}</td>
                    <td class="text-sm">{{ $grn['branch_name'] ?? 'N/A' }}</td>
                    <td class="text-right font-semibold">Rs. {{ number_format($grn['total_purchase_value'] ?? 0, 2) }}</td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($grn['status'] ?? '') === 'verified') status-active
                            @elseif(($grn['status'] ?? '') === 'received') status-confirmed
                            @else status-pending
                            @endif">
                            {{ ucfirst($grn['status'] ?? 'pending') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($grn['payment_status'] ?? '') === 'paid') status-active
                            @elseif(($grn['payment_status'] ?? '') === 'partial') status-pending
                            @else status-out-of-stock
                            @endif">
                            {{ ucfirst($grn['payment_status'] ?? 'unpaid') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <!-- Detailed View - Complete GRN Information -->
    <div class="avoid-break">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 12%">GRN Number</th>
                    <th style="width: 8%">Date</th>
                    <th style="width: 15%">Supplier</th>
                    <th style="width: 12%">Branch</th>
                    <th style="width: 10%">Items</th>
                    <th style="width: 10%">Total Amount</th>
                    <th style="width: 10%">Paid</th>
                    <th style="width: 10%">Outstanding</th>
                    <th style="width: 8%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grns as $index => $grn)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-semibold">{{ $grn['grn_number'] ?? 'N/A' }}</td>
                    <td class="text-sm">{{ isset($grn['received_date']) ? \Carbon\Carbon::parse($grn['received_date'])->format('M j, Y') : 'N/A' }}</td>
                    <td>{{ $grn['supplier_name'] ?? 'N/A' }}</td>
                    <td class="text-sm">{{ $grn['branch_name'] ?? 'N/A' }}</td>
                    <td class="text-center">{{ $grn['items_count'] ?? 0 }}</td>
                    <td class="text-right font-semibold">Rs. {{ number_format($grn['total_purchase_value'] ?? 0, 2) }}</td>
                    <td class="text-right text-success">Rs. {{ number_format($grn['paid_amount'] ?? 0, 2) }}</td>
                    <td class="text-right text-danger">Rs. {{ number_format($grn['outstanding_amount'] ?? 0, 2) }}</td>
                    <td class="text-center">
                        <span class="status-badge
                            @if(($grn['status'] ?? '') === 'verified') status-active
                            @elseif(($grn['status'] ?? '') === 'received') status-confirmed
                            @else status-pending
                            @endif">
                            {{ ucfirst($grn['status'] ?? 'pending') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if($viewType === 'detailed')
    <!-- Additional Analysis for Detailed View -->
    <div class="page-break">
        <h3 class="mb-4" style="font-size: 14px; font-weight: bold; color: #374151;">Payment Analysis</h3>

        <!-- Outstanding Payments -->
        @php
            $outstandingGRNs = collect($grns)->where('outstanding_amount', '>', 0)->sortByDesc('outstanding_amount');
        @endphp

        @if($outstandingGRNs->count() > 0)
        <div class="avoid-break mb-4">
            <h4 class="mb-2" style="font-size: 12px; font-weight: bold; color: #dc2626;">Outstanding Payments ({{ $outstandingGRNs->count() }})</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>GRN Number</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Outstanding</th>
                        <th>Days Overdue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outstandingGRNs->take(15) as $grn)
                    @php
                        $daysOverdue = isset($grn['received_date']) ?
                            \Carbon\Carbon::parse($grn['received_date'])->diffInDays(now()) : 0;
                    @endphp
                    <tr>
                        <td class="font-semibold">{{ $grn['grn_number'] ?? 'N/A' }}</td>
                        <td>{{ $grn['supplier_name'] ?? 'N/A' }}</td>
                        <td class="text-sm">{{ isset($grn['received_date']) ? \Carbon\Carbon::parse($grn['received_date'])->format('M j, Y') : 'N/A' }}</td>
                        <td class="text-right">Rs. {{ number_format($grn['total_purchase_value'] ?? 0, 2) }}</td>
                        <td class="text-right text-success">Rs. {{ number_format($grn['paid_amount'] ?? 0, 2) }}</td>
                        <td class="text-right text-danger font-semibold">Rs. {{ number_format($grn['outstanding_amount'] ?? 0, 2) }}</td>
                        <td class="text-center">
                            <span class="@if($daysOverdue > 30) text-danger @elseif($daysOverdue > 15) text-warning @else text-primary @endif">
                                {{ $daysOverdue }} days
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Top Suppliers by Volume -->
        <div class="avoid-break mb-4">
            <h4 class="mb-2" style="font-size: 12px; font-weight: bold; color: #4b5563;">Top 10 Suppliers by Purchase Volume</h4>
            @php
                $topSuppliers = collect($grns)->groupBy('supplier_name')
                    ->map(function($items, $supplier) {
                        return [
                            'supplier' => $supplier ?: 'Unknown',
                            'count' => $items->count(),
                            'total_value' => $items->sum('total_purchase_value'),
                            'avg_value' => $items->avg('total_purchase_value')
                        ];
                    })->sortByDesc('total_value')->take(10);
            @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>GRNs Count</th>
                        <th>Total Purchase Value</th>
                        <th>Average GRN Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topSuppliers as $supplier)
                    <tr>
                        <td class="font-semibold">{{ $supplier['supplier'] }}</td>
                        <td class="text-center">{{ number_format($supplier['count']) }}</td>
                        <td class="text-right font-semibold">Rs. {{ number_format($supplier['total_value'], 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($supplier['avg_value'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
                <td class="text-sm">{{ $grn->supplier->name ?? 'N/A' }}</td>
                <td class="text-sm">{{ $grn->branch->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $grn->items ? $grn->items->count() : 0 }}</td>
                <td class="text-right font-semibold">
                    Rs. {{ number_format($grn->total_amount ?? 0, 2) }}
                </td>
                <td class="text-center">
                    @php
                        $statusClass = match($grn->status) {
                            'pending' => 'status-pending',
                            'received' => 'status-confirmed',
                            'verified' => 'status-active',
                            'rejected' => 'status-out-of-stock',
                            default => 'status-badge'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ ucfirst($grn->status) }}
                    </span>
                </td>
                <td class="text-sm">{{ $grn->receivedBy->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Additional Analysis (if detailed view) -->
@if($viewType === 'detailed' && $grns->count() > 0)
<div class="page-break"></div>

<div class="mt-4">
    <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 15px; color: #2563eb;">GRN Analysis</h3>

    <!-- Supplier Performance Analysis -->
    @php
        $supplierPerformance = $grns->groupBy(function($grn) {
            return $grn->supplier->name ?? 'Unknown Supplier';
        })->map(function($supplierGrns, $supplierName) {
            return [
                'supplier' => $supplierName,
                'total_grns' => $supplierGrns->count(),
                'pending' => $supplierGrns->where('status', 'pending')->count(),
                'received' => $supplierGrns->where('status', 'received')->count(),
                'verified' => $supplierGrns->where('status', 'verified')->count(),
                'total_value' => $supplierGrns->sum('total_amount'),
                'total_items' => $supplierGrns->sum(function($grn) {
                    return $grn->items ? $grn->items->count() : 0;
                }),
            ];
        })->sortByDesc('total_value');
    @endphp

    @if($supplierPerformance->count() > 0)
    <div class="avoid-break">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Supplier Performance Analysis</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th class="text-center">Total GRNs</th>
                    <th class="text-center">Pending</th>
                    <th class="text-center">Received</th>
                    <th class="text-center">Verified</th>
                    <th class="text-center">Items</th>
                    <th class="text-right">Total Value</th>
                    <th class="text-center">Success Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supplierPerformance as $supplier)
                <tr>
                    <td class="font-semibold">{{ $supplier['supplier'] }}</td>
                    <td class="text-center">{{ $supplier['total_grns'] }}</td>
                    <td class="text-center text-warning">{{ $supplier['pending'] }}</td>
                    <td class="text-center text-primary">{{ $supplier['received'] }}</td>
                    <td class="text-center text-success">{{ $supplier['verified'] }}</td>
                    <td class="text-center">{{ $supplier['total_items'] }}</td>
                    <td class="text-right">Rs. {{ number_format($supplier['total_value'], 2) }}</td>
                    <td class="text-center">
                        @php
                            $successRate = $supplier['total_grns'] > 0 ?
                                (($supplier['received'] + $supplier['verified']) / $supplier['total_grns']) * 100 : 0;
                        @endphp
                        <span class="font-semibold">{{ number_format($successRate, 1) }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Branch Receipt Analysis -->
    @php
        $branchReceipts = $grns->groupBy(function($grn) {
            return $grn->branch->name ?? 'Unknown Branch';
        })->map(function($branchGrns, $branchName) {
            return [
                'branch' => $branchName,
                'total_grns' => $branchGrns->count(),
                'total_value' => $branchGrns->sum('total_amount'),
                'verified_grns' => $branchGrns->where('status', 'verified')->count(),
                'verified_value' => $branchGrns->where('status', 'verified')->sum('total_amount'),
                'avg_grn_value' => $branchGrns->avg('total_amount'),
            ];
        })->sortByDesc('total_value');
    @endphp

    @if($branchReceipts->count() > 0)
    <div class="avoid-break mt-4">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Branch Receipt Analysis</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Branch</th>
                    <th class="text-center">Total GRNs</th>
                    <th class="text-right">Total Value</th>
                    <th class="text-center">Verified GRNs</th>
                    <th class="text-right">Verified Value</th>
                    <th class="text-right">Avg GRN Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branchReceipts as $branch)
                <tr>
                    <td class="font-semibold">{{ $branch['branch'] }}</td>
                    <td class="text-center">{{ $branch['total_grns'] }}</td>
                    <td class="text-right">Rs. {{ number_format($branch['total_value'], 2) }}</td>
                    <td class="text-center text-success">{{ $branch['verified_grns'] }}</td>
                    <td class="text-right text-success">Rs. {{ number_format($branch['verified_value'], 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($branch['avg_grn_value'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Monthly Trend Analysis -->
    @php
        $monthlyTrends = $grns->groupBy(function($grn) {
            return \Carbon\Carbon::parse($grn->created_at)->format('Y-m');
        })->map(function($monthGrns, $month) {
            return [
                'month' => $month,
                'month_name' => \Carbon\Carbon::parse($month . '-01')->format('M Y'),
                'total_grns' => $monthGrns->count(),
                'total_value' => $monthGrns->sum('total_amount'),
                'verified_grns' => $monthGrns->where('status', 'verified')->count(),
                'suppliers' => $monthGrns->pluck('supplier.name')->filter()->unique()->count(),
            ];
        })->sortBy('month')->take(12);
    @endphp

    @if($monthlyTrends->count() > 1)
    <div class="avoid-break mt-4">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Monthly Receipt Trends</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-center">Total GRNs</th>
                    <th class="text-right">Total Value</th>
                    <th class="text-center">Verified</th>
                    <th class="text-center">Suppliers</th>
                    <th class="text-right">Avg Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlyTrends as $trend)
                <tr>
                    <td class="font-semibold">{{ $trend['month_name'] }}</td>
                    <td class="text-center">{{ $trend['total_grns'] }}</td>
                    <td class="text-right">Rs. {{ number_format($trend['total_value'], 2) }}</td>
                    <td class="text-center text-success">{{ $trend['verified_grns'] }}</td>
                    <td class="text-center">{{ $trend['suppliers'] }}</td>
                    <td class="text-right">
                        Rs. {{ number_format($trend['total_grns'] > 0 ? $trend['total_value'] / $trend['total_grns'] : 0, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recent GRN Details -->
    @php
        $recentGrns = $grns->sortByDesc('created_at')->take(6);
    @endphp

    @if($recentGrns->count() > 0)
    <div class="avoid-break mt-4">
        <h4 style="font-size: 12px; font-weight: 600; margin-bottom: 10px;">Recent GRN Details</h4>

        @foreach($recentGrns as $grn)
        <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 10px; background: #fafafa;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div>
                    <span style="font-weight: 600; color: #2563eb;">{{ $grn->grn_number }}</span>
                    <span style="margin-left: 10px; font-size: 10px; color: #6b7280;">
                        {{ \Carbon\Carbon::parse($grn->created_at)->format('M j, Y g:i A') }}
                    </span>
                </div>
                <div>
                    @php
                        $statusClass = match($grn->status) {
                            'pending' => 'status-pending',
                            'received' => 'status-confirmed',
                            'verified' => 'status-active',
                            'rejected' => 'status-out-of-stock',
                            default => 'status-badge'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ ucfirst($grn->status) }}
                    </span>
                </div>
            </div>

            <div style="font-size: 10px; color: #6b7280; margin-bottom: 6px;">
                <strong>Supplier:</strong> {{ $grn->supplier->name ?? 'N/A' }}
                | <strong>Branch:</strong> {{ $grn->branch->name ?? 'N/A' }}
                | <strong>Value:</strong> Rs. {{ number_format($grn->total_amount ?? 0, 2) }}
            </div>

            @if($grn->items && $grn->items->count() > 0)
            <div style="font-size: 10px;">
                <strong>Items ({{ $grn->items->count() }}):</strong>
                @foreach($grn->items->take(3) as $item)
                    <span style="background: #e2e8f0; padding: 2px 6px; border-radius: 3px; margin-right: 4px; display: inline-block; margin-bottom: 2px;">
                        {{ $item->item->name ?? 'N/A' }} ({{ number_format($item->received_quantity ?? 0, 2) }})
                    </span>
                @endforeach
                @if($grn->items->count() > 3)
                    <span style="color: #6b7280;">... and {{ $grn->items->count() - 3 }} more</span>
                @endif
            </div>
            @endif

            @if($grn->notes)
            <div style="font-size: 10px; color: #6b7280; margin-top: 6px; font-style: italic;">
                <strong>Notes:</strong> {{ Str::limit($grn->notes, 100) }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif

@if($grns->count() == 0)
<div style="text-align: center; padding: 40px; color: #6b7280;">
    <div style="font-size: 14px; margin-bottom: 10px;">No GRN data found</div>
    <div style="font-size: 12px;">Please adjust your filters and try again.</div>
</div>
@endif
@endsection
