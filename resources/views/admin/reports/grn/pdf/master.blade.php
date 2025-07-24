<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRN Master Report</title>
    <style>
        @page {
            size: A4;
            margin: 0.5in;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2563eb;
        }

        .header h2 {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .header .company-info {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .report-info div {
            flex: 1;
        }

        .report-info strong {
            display: block;
            margin-bottom: 5px;
            color: #374151;
        }

        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 15px;
        }

        .summary-card {
            flex: 1;
            text-align: center;
            padding: 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background-color: #f8fafc;
        }

        .summary-card h3 {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
        }

        .table-container {
            width: 100%;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 4px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
            font-size: 9px;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .status {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status.completed {
            background-color: #dcfce7;
            color: #166534;
        }

        .status.approved {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .status.received {
            background-color: #fef3c7;
            color: #d97706;
        }

        .status.pending {
            background-color: #e5e7eb;
            color: #374151;
        }

        .amount {
            text-align: right;
            font-weight: 500;
        }

        .positive {
            color: #059669;
        }

        .negative {
            color: #dc2626;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }

        .page-break {
            page-break-before: always;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Report Header -->
    <div class="header">
        <h1>{{ $organizationName ?? 'Restaurant Management System' }}</h1>
        <h2>GRN Master Report</h2>
        <div class="company-info">
            @if(isset($organizationAddress))
                <div>{{ $organizationAddress }}</div>
            @endif
            @if(isset($organizationPhone))
                <div>Phone: {{ $organizationPhone }}</div>
            @endif
        </div>
    </div>

    <!-- Report Information -->
    <div class="report-info">
        <div>
            <strong>Report Date:</strong>
            {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}
        </div>
        <div>
            <strong>Date Range:</strong>
            @if(isset($dateFrom) && isset($dateTo))
                {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
            @else
                All Dates
            @endif
        </div>
        <div>
            <strong>Branch:</strong>
            {{ $branchName ?? 'All Branches' }}
        </div>
        <div>
            <strong>Generated By:</strong>
            {{ $generatedBy ?? 'System Admin' }}
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3>Total GRNs</h3>
            <div class="value">{{ $summary['total_grns'] ?? 0 }}</div>
        </div>
        <div class="summary-card">
            <h3>Total Value</h3>
            <div class="value">${{ number_format($summary['total_value'] ?? 0, 2) }}</div>
        </div>
        <div class="summary-card">
            <h3>Total Payments</h3>
            <div class="value">${{ number_format($summary['total_payments'] ?? 0, 2) }}</div>
        </div>
        <div class="summary-card">
            <h3>Total Discounts</h3>
            <div class="value">${{ number_format($summary['total_discounts'] ?? 0, 2) }}</div>
        </div>
        <div class="summary-card">
            <h3>Outstanding Balance</h3>
            <div class="value">${{ number_format(($summary['total_value'] ?? 0) - ($summary['total_payments'] ?? 0), 2) }}</div>
        </div>
    </div>

    <!-- Report Data Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">GRN Number</th>
                    <th style="width: 8%;">Date</th>
                    <th style="width: 15%;">Supplier</th>
                    <th style="width: 12%;">Branch</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 10%;">Total Value</th>
                    <th style="width: 10%;">Payments</th>
                    <th style="width: 10%;">Discounts</th>
                    <th style="width: 10%;">Balance</th>
                    <th style="width: 7%;">Items</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $grn)
                <tr>
                    <td class="font-bold">{{ $grn['grn_number'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($grn['date'])->format('M d, Y') }}</td>
                    <td>{{ $grn['supplier_name'] }}</td>
                    <td>{{ $grn['branch_name'] }}</td>
                    <td>
                        <span class="status {{ strtolower($grn['status']) }}">
                            {{ ucfirst($grn['status']) }}
                        </span>
                    </td>
                    <td class="amount">${{ number_format($grn['total_value'], 2) }}</td>
                    <td class="amount">${{ number_format($grn['total_payments'], 2) }}</td>
                    <td class="amount">${{ number_format($grn['total_discounts'], 2) }}</td>
                    <td class="amount {{ $grn['balance'] > 0 ? 'negative' : 'positive' }}">
                        ${{ number_format($grn['balance'], 2) }}
                    </td>
                    <td class="text-center">{{ $grn['item_count'] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Additional Statistics -->
    @if(isset($additionalStats))
    <div class="page-break">
        <h2 class="mb-20 text-center">Additional Statistics</h2>

        <!-- Payment Status Analysis -->
        <div class="mb-20">
            <h3 class="mb-10 font-bold">Payment Status Analysis</h3>
            <table style="width: 60%; margin: 0 auto;">
                <thead>
                    <tr>
                        <th>Payment Status</th>
                        <th>Count</th>
                        <th>Total Value</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($additionalStats['payment_status'] ?? [] as $status)
                    <tr>
                        <td>{{ $status['status'] }}</td>
                        <td class="text-center">{{ $status['count'] }}</td>
                        <td class="amount">${{ number_format($status['total_value'], 2) }}</td>
                        <td class="text-center">{{ number_format($status['percentage'], 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Top Suppliers -->
        <div class="mb-20">
            <h3 class="mb-10 font-bold">Top Suppliers by Value</h3>
            <table style="width: 70%; margin: 0 auto;">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>GRN Count</th>
                        <th>Total Value</th>
                        <th>Average Order Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($additionalStats['top_suppliers'] ?? [] as $supplier)
                    <tr>
                        <td>{{ $supplier['supplier_name'] }}</td>
                        <td class="text-center">{{ $supplier['grn_count'] }}</td>
                        <td class="amount">${{ number_format($supplier['total_value'], 2) }}</td>
                        <td class="amount">${{ number_format($supplier['avg_order_value'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Report Footer -->
    <div class="footer">
        <div>Generated on {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</div>
        <div>This is a computer-generated report. No signature required.</div>
        <div style="margin-top: 10px;">
            <strong>{{ $organizationName ?? 'Restaurant Management System' }}</strong> -
            Goods Receipt Note Master Report
        </div>
    </div>
</body>
</html>
