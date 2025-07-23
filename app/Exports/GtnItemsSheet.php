<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\GoodsTransferItem;
use Illuminate\Support\Facades\Auth;

class GtnItemsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStrictNullComparison
{
    protected $gtnIds;
    protected $dateFrom;
    protected $dateTo;
    protected $filters;

    public function __construct($gtnIds = null, $dateFrom = null, $dateTo = null, $filters = [])
    {
        $this->gtnIds = $gtnIds;
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

        $query = GoodsTransferItem::with([
            'goodsTransferNote.fromBranch',
            'goodsTransferNote.toBranch',
            'goodsTransferNote.organization',
            'item.category',
            'inspectedBy'
        ]);

        // Apply organization filter through GTN relationship
        if (!$isSuperAdmin && $orgId) {
            $query->whereHas('goodsTransferNote', function($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            });
        }

        // Apply specific GTN IDs if provided
        if ($this->gtnIds) {
            $query->whereIn('gtn_id', $this->gtnIds);
        }

        // Apply date range through GTN relationship
        if ($this->dateFrom && $this->dateTo) {
            $query->whereHas('goodsTransferNote', function($q) {
                $q->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);
            });
        }

        return $query->orderBy('gtn_id', 'desc')
                    ->orderBy('gtn_item_id', 'asc')
                    ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'GTN ID',
            'GTN Number',
            'Organization',
            'From Branch',
            'To Branch',
            'Item ID',
            'Item Code',
            'Item Name',
            'Category',
            'Batch No',
            'Transfer Qty',
            'Received Qty',
            'Damaged Qty',
            'Accepted Qty',
            'Rejected Qty',
            'Transfer Price',
            'Line Total',
            'Item Status',
            'Acceptance Rate (%)',
            'Rejection Rate (%)',
            'Accepted Value',
            'Rejected Value',
            'Expiry Date',
            'Inspected By',
            'Inspected At',
            'Notes',
            'Rejection Reason',
            'Quality Notes'
        ];
    }

    /**
     * @param mixed $gtnItem
     * @return array
     */
    public function map($gtnItem): array
    {
        return [
            $gtnItem->gtn_id,
            $gtnItem->goodsTransferNote->gtn_number ?? 'N/A',
            $gtnItem->goodsTransferNote->organization->name ?? 'N/A',
            $gtnItem->goodsTransferNote->fromBranch->name ?? 'N/A',
            $gtnItem->goodsTransferNote->toBranch->name ?? 'N/A',
            $gtnItem->item_id,
            $gtnItem->item_code,
            $gtnItem->item_name,
            $gtnItem->item->category->name ?? 'N/A',
            $gtnItem->batch_no ?? 'N/A',
            $gtnItem->transfer_quantity,
            $gtnItem->received_quantity,
            $gtnItem->damaged_quantity,
            $gtnItem->quantity_accepted,
            $gtnItem->quantity_rejected,
            $gtnItem->transfer_price,
            $gtnItem->line_total,
            $gtnItem->item_status,
            $gtnItem->acceptance_rate ? number_format($gtnItem->acceptance_rate, 2) : '0.00',
            $gtnItem->rejection_rate ? number_format($gtnItem->rejection_rate, 2) : '0.00',
            $gtnItem->accepted_value,
            $gtnItem->rejected_value,
            $gtnItem->expiry_date ? $gtnItem->expiry_date->format('Y-m-d') : 'N/A',
            $gtnItem->inspectedBy->name ?? 'N/A',
            $gtnItem->inspected_at ? $gtnItem->inspected_at->format('Y-m-d H:i:s') : 'N/A',
            $gtnItem->notes ?? 'N/A',
            $gtnItem->item_rejection_reason ?? 'N/A',
            is_array($gtnItem->quality_notes) ? implode('; ', $gtnItem->quality_notes) : ($gtnItem->quality_notes ?? 'N/A')
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'GTN Items Data';
    }
}
