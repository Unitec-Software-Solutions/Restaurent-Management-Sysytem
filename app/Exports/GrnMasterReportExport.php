<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GrnMasterReportExport implements WithMultipleSheets
{
    protected $reportData;
    protected $summary;
    protected $filters;

    public function __construct(array $reportData, array $summary = [], array $filters = [])
    {
        $this->reportData = $reportData;
        $this->summary = $summary;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Main Report Sheet
        $sheets[] = new GrnMasterReportSheet($this->reportData, $this->summary, $this->filters);

        // Summary Sheet
        if (!empty($this->summary)) {
            $sheets[] = new GrnMasterSummarySheet($this->summary, $this->filters);
        }

        // Payment Analysis Sheet (if data available)
        if (!empty($this->reportData)) {
            $sheets[] = new GrnPaymentAnalysisSheet($this->reportData);
        }

        return $sheets;
    }
}

class GrnMasterReportSheet implements FromArray, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $reportData;
    protected $summary;
    protected $filters;

    public function __construct(array $reportData, array $summary = [], array $filters = [])
    {
        $this->reportData = $reportData;
        $this->summary = $summary;
        $this->filters = $filters;
    }

    public function array(): array
    {
        return $this->reportData;
    }

    public function headings(): array
    {
        return [
            'GRN Number',
            'Date',
            'Supplier Name',
            'Branch Name',
            'Status',
            'Total Value ($)',
            'Total Payments ($)',
            'Total Discounts ($)',
            'Outstanding Balance ($)',
            'Item Count',
            'Created By',
            'Approved By',
            'Approval Date',
            'Notes'
        ];
    }

    public function map($grn): array
    {
        return [
            $grn['grn_number'] ?? '',
            isset($grn['date']) ? \Carbon\Carbon::parse($grn['date'])->format('Y-m-d') : '',
            $grn['supplier_name'] ?? '',
            $grn['branch_name'] ?? '',
            ucfirst($grn['status'] ?? ''),
            $grn['total_value'] ?? 0,
            $grn['total_payments'] ?? 0,
            $grn['total_discounts'] ?? 0,
            $grn['balance'] ?? 0,
            $grn['item_count'] ?? 0,
            $grn['created_by_name'] ?? '',
            $grn['approved_by_name'] ?? '',
            isset($grn['approved_at']) ? \Carbon\Carbon::parse($grn['approved_at'])->format('Y-m-d') : '',
            $grn['notes'] ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set header styles
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Set data rows styles
        $lastRow = count($this->reportData) + 1;
        $sheet->getStyle("A2:N{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Format currency columns
        $sheet->getStyle("F2:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        // Format date columns
        $sheet->getStyle("B2:B{$lastRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        $sheet->getStyle("M2:M{$lastRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');

        // Auto-size columns
        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set row height
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Add filter to the data
        $sheet->setAutoFilter("A1:N{$lastRow}");

        return [];
    }

    public function title(): string
    {
        return 'GRN Master Report';
    }
}

class GrnMasterSummarySheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $summary;
    protected $filters;

    public function __construct(array $summary, array $filters = [])
    {
        $this->summary = $summary;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $data = [];

        // Report Information
        $data[] = ['Report Information', ''];
        $data[] = ['Generated On', now()->format('Y-m-d H:i:s')];
        $data[] = ['Date Range', ($this->filters['date_from'] ?? 'All') . ' to ' . ($this->filters['date_to'] ?? 'All')];
        $data[] = ['Branch', $this->filters['branch_name'] ?? 'All Branches'];
        $data[] = ['Status Filter', $this->filters['status'] ?? 'All Status'];
        $data[] = ['', ''];

        // Summary Statistics
        $data[] = ['Summary Statistics', ''];
        $data[] = ['Total GRNs', $this->summary['total_grns'] ?? 0];
        $data[] = ['Total Value', $this->summary['total_value'] ?? 0];
        $data[] = ['Total Payments', $this->summary['total_payments'] ?? 0];
        $data[] = ['Total Discounts', $this->summary['total_discounts'] ?? 0];
        $data[] = ['Outstanding Balance', ($this->summary['total_value'] ?? 0) - ($this->summary['total_payments'] ?? 0)];
        $data[] = ['Average GRN Value', ($this->summary['total_grns'] ?? 0) > 0 ? ($this->summary['total_value'] ?? 0) / ($this->summary['total_grns'] ?? 1) : 0];
        $data[] = ['Average Discount %', ($this->summary['total_value'] ?? 0) > 0 ? (($this->summary['total_discounts'] ?? 0) / ($this->summary['total_value'] ?? 1)) * 100 : 0];
        $data[] = ['Payment Coverage %', ($this->summary['total_value'] ?? 0) > 0 ? (($this->summary['total_payments'] ?? 0) / ($this->summary['total_value'] ?? 1)) * 100 : 0];

        return $data;
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styles
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Section headers (rows 2, 8)
        $sheet->getStyle('A2:B2')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB']
            ]
        ]);

        $sheet->getStyle('A8:B8')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB']
            ]
        ]);

        // Format currency values
        $sheet->getStyle('B9:B16')->getNumberFormat()->setFormatCode('#,##0.00');

        // Format percentage values
        $sheet->getStyle('B14:B15')->getNumberFormat()->setFormatCode('#,##0.00"%"');

        // Auto-size columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);

        return [];
    }

    public function title(): string
    {
        return 'Summary';
    }
}

class GrnPaymentAnalysisSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $reportData;

    public function __construct(array $reportData)
    {
        $this->reportData = $reportData;
    }

    public function array(): array
    {
        $data = [];

        // Payment Status Analysis
        $paymentAnalysis = $this->analyzePaymentStatus();
        foreach ($paymentAnalysis as $analysis) {
            $data[] = [
                $analysis['payment_status'],
                $analysis['count'],
                $analysis['total_value'],
                $analysis['percentage'],
                $analysis['avg_value']
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Payment Status',
            'Count',
            'Total Value ($)',
            'Percentage (%)',
            'Average Value ($)'
        ];
    }

    protected function analyzePaymentStatus(): array
    {
        $analysis = [];
        $totalValue = array_sum(array_column($this->reportData, 'total_value'));

        // Group by payment status
        $grouped = [];
        foreach ($this->reportData as $grn) {
            $balance = $grn['balance'] ?? 0;
            if ($balance <= 0) {
                $status = 'Fully Paid';
            } elseif ($grn['total_payments'] > 0) {
                $status = 'Partially Paid';
            } else {
                $status = 'Unpaid';
            }

            if (!isset($grouped[$status])) {
                $grouped[$status] = [
                    'count' => 0,
                    'total_value' => 0
                ];
            }

            $grouped[$status]['count']++;
            $grouped[$status]['total_value'] += $grn['total_value'] ?? 0;
        }

        foreach ($grouped as $status => $data) {
            $analysis[] = [
                'payment_status' => $status,
                'count' => $data['count'],
                'total_value' => $data['total_value'],
                'percentage' => $totalValue > 0 ? ($data['total_value'] / $totalValue) * 100 : 0,
                'avg_value' => $data['count'] > 0 ? $data['total_value'] / $data['count'] : 0
            ];
        }

        return $analysis;
    }

    public function styles(Worksheet $sheet)
    {
        // Header styles
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DC2626']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Format currency columns
        $lastRow = count($this->analyzePaymentStatus()) + 1;
        $sheet->getStyle("C2:C{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("E2:E{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        // Format percentage column
        $sheet->getStyle("D2:D{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00"%"');

        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }

    public function title(): string
    {
        return 'Payment Analysis';
    }
}
