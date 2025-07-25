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
