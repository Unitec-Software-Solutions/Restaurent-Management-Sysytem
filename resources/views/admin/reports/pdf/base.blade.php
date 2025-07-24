<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle ?? 'Report' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 0;
            margin: 0;
            background: white;
            position: relative;
            box-sizing: border-box;
        }

        /* Header Styles */
        .report-header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .org-info {
            text-align: center;
            margin-bottom: 15px;
        }

        .org-name {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .org-address {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .report-subtitle {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 15px;
        }

        .report-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #6b7280;
        }

        /* Filter Summary */
        .filters-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .filters-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 8px;
        }

        .filter-item {
            font-size: 10px;
            color: #6b7280;
        }

        .filter-label {
            font-weight: 600;
            color: #374151;
        }

        /* Summary Cards */
        .summary-section {
            margin-bottom: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }

        .summary-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .data-table th {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 9px;
            text-transform: uppercase;
        }

        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            vertical-align: top;
        }

        .data-table tr:nth-child(even) {
            background: #fafafa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-semibold {
            font-weight: 600;
        }

        .text-sm {
            font-size: 10px;
        }

        .text-xs {
            font-size: 9px;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-in-stock {
            background: #dcfce7;
            color: #166534;
        }

        .status-low-stock {
            background: #fef3c7;
            color: #92400e;
        }

        .status-out-of-stock {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Footer */
        .report-footer {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 9px;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Page Break */
        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        /* Utilities */
        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .text-primary {
            color: #2563eb;
        }

        .text-success {
            color: #059669;
        }

        .text-warning {
            color: #d97706;
        }

        .text-danger {
            color: #dc2626;
        }

        .bg-light {
            background: #f8fafc;
        }

        /* Print specific */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .page {
                margin: 0;
                padding: 0;
                width: 210mm;
                min-height: 297mm;
                box-sizing: border-box;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header Section -->
        <div class="report-header">
            <div class="org-info">
                @if($organization)
                    <div class="org-name">{{ $organization->name }}</div>
                    <div class="org-address">{{ $organization->address }}</div>
                @else
                    <div class="org-name">Restaurant Management System</div>
                    <div class="org-address">Super Admin Report</div>
                @endif
            </div>

            <div class="report-title">{{ $reportTitle }}</div>
            <div class="report-subtitle">{{ $reportSubtitle }}</div>

            <div class="report-meta">
                <div>
                    Generated: {{ $generatedAt->format('F j, Y g:i A') }}
                </div>
                <div>
                    By: {{ $user->name ?? 'System' }}
                </div>
            </div>
        </div>

        @yield('content')

        <!-- Footer -->
        <div class="report-footer">
            <div>
                {{ $reportTitle }} - Generated on {{ $generatedAt->format('Y-m-d H:i:s') }}
            </div>
            <div>
                Page <span class="pagenum"></span>
            </div>
        </div>
    </div>
</body>
</html>
