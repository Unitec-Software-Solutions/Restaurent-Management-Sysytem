<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\GrnItem;
use Illuminate\Support\Facades\Auth;

class GrnItemsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStrictNullComparison
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
        $isSuperAdmin = $user && ($user->is_super_admin ?? false);
        $orgId = $isSuperAdmin ? null : ($user->organization_id ?? null);

        $query = GrnItem::with([
            'grn.supplier',
            'grn.branch',
            'grn.organization',
            'item.category'
        ]);

        // Apply organization filter through GRN relationship
        if (!$isSuperAdmin && $orgId) {
            $query->whereHas('grn', function($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            });
        }

        // Apply specific GRN IDs if provided
        if ($this->grnIds) {
            $query->whereIn('grn_id', $this->grnIds);
        }

        // Apply date range through GRN relationship
        if ($this->dateFrom && $this->dateTo) {
            $query->whereHas('grn', function($q) {
                $q->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);
            });
        }

        return $query->orderBy('grn_id', 'desc')
                    ->orderBy('grn_item_id', 'asc')
                    ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'GRN ID',
            'GRN Number',
            'Organization',
            'Branch',
            'Supplier',
            'Item ID',
            'Item Code',
            'Item Name',
            'Category',
            'Batch No',
            'Ordered Qty',
            'Received Qty',
            'Accepted Qty',
            'Free Received Qty',
            'Total to Stock',
            'Rejected Qty',
            'Rejection Reason',
            'Buying Price',
            'Line Total',
            'Discount Received',
            'Manufacturing Date',
            'Expiry Date',
            'Days Until Expiry',
            'Status'
        ];
    }

    /**
     * @param mixed $grnItem
     * @return array
     */
    public function map($grnItem): array
    {
        return [
            $grnItem->grn_id,
            $grnItem->grn->grn_number ?? 'N/A',
            $grnItem->grn->organization->name ?? 'N/A',
            $grnItem->grn->branch->name ?? 'N/A',
            $grnItem->grn->supplier->name ?? 'N/A',
            $grnItem->item_id,
            $grnItem->item_code,
            $grnItem->item_name,
            $grnItem->item->category->name ?? 'N/A',
            $grnItem->batch_no ?? 'N/A',
            $grnItem->ordered_quantity,
            $grnItem->received_quantity,
            $grnItem->accepted_quantity,
            $grnItem->free_received_quantity,
            $grnItem->total_to_stock,
            $grnItem->rejected_quantity,
            $grnItem->rejection_reason ?? 'N/A',
            $grnItem->buying_price,
            $grnItem->line_total,
            $grnItem->discount_received,
            $grnItem->manufacturing_date ? $grnItem->manufacturing_date->format('Y-m-d') : 'N/A',
            $grnItem->expiry_date ? $grnItem->expiry_date->format('Y-m-d') : 'N/A',
            $grnItem->days_until_expiry ?? 'N/A',
            $grnItem->is_complete ? 'Complete' : ($grnItem->is_partial ? 'Partial' : 'Pending')
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'GRN Items Data';
    }
}
