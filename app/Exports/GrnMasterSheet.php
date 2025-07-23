<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\GrnMaster;
use Illuminate\Support\Facades\Auth;

class GrnMasterSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStrictNullComparison
{
    protected $grnIds;
    protected $dateFrom;
    protected $dateTo;
    protected $filters;

    public function __construct($grnIds = null, $dateFrom = null, $dateTo = null, $filters = [])
    {
        $this->grnIds = $grnIds;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $user = Auth::guard('admin')->user();
        $isSuperAdmin = $user->is_super_admin ?? false;
        $orgId = $isSuperAdmin ? null : $user->organization_id;

        $query = GrnMaster::with([
            'supplier',
            'branch',
            'organization',
            'receivedByUser',
            'verifiedByUser',
            'purchaseOrder'
        ]);

        // Apply organization filter for non-super admins
        if (!$isSuperAdmin && $orgId) {
            $query->where('organization_id', $orgId);
        }

        // Apply specific GRN IDs if provided
        if ($this->grnIds) {
            $query->whereIn('grn_id', $this->grnIds);
        }

        // Apply date range
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);
        }

        // Apply additional filters with null/empty value checks
        foreach ($this->filters as $column => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'GRN ID',
            'GRN Number',
            'PO Number',
            'Organization',
            'Branch',
            'Supplier',
            'Status',
            'Sub Total',
            'Discount Amount',
            'Final Total',
            'Paid Amount',
            'Balance Amount',
            'Payment Status',
            'Received By',
            'Verified By',
            'Received Date',
            'Verified Date',
            'Created Date',
            'Notes'
        ];
    }

    /**
     * @param mixed $grn
     * @return array
     */
    public function map($grn): array
    {
        return [
            $grn->grn_id,
            $grn->grn_number,
            $grn->purchaseOrder->po_number ?? 'N/A',
            $grn->organization->name ?? 'N/A',
            $grn->branch->name ?? 'N/A',
            $grn->supplier->name ?? 'N/A',
            $grn->status,
            $grn->sub_total,
            $grn->grand_discount_amount,
            $grn->final_total,
            $grn->paid_amount,
            $grn->balance_amount,
            $grn->payment_status,
            $grn->receivedByUser->name ?? 'N/A',
            $grn->verifiedByUser->name ?? 'N/A',
            $grn->received_date ? $grn->received_date->format('Y-m-d H:i:s') : 'N/A',
            $grn->verified_date ? $grn->verified_date->format('Y-m-d H:i:s') : 'N/A',
            $grn->created_at ? $grn->created_at->format('Y-m-d H:i:s') : 'N/A',
            $grn->notes ?? 'N/A'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'GRN Master Data';
    }
}
