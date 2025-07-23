<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\StockReleaseNoteItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SrnItemsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStrictNullComparison
{
    protected $srnIds;
    protected $dateFrom;
    protected $dateTo;
    protected $filters;

    public function __construct($srnIds = null, $dateFrom = null, $dateTo = null, $filters = [])
    {
        $this->srnIds = $srnIds;
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

        $query = StockReleaseNoteItem::with([
            'stockReleaseNote.branch',
            'stockReleaseNote.organization',
            'item.category'
        ]);

        // Apply organization filter through SRN relationship
        if (!$isSuperAdmin && $orgId) {
            $query->whereHas('stockReleaseNote', function($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            });
        }

        // Apply specific SRN IDs if provided
        if ($this->srnIds) {
            $query->whereIn('srn_id', $this->srnIds);
        }

        // Apply date range through SRN relationship
        if ($this->dateFrom && $this->dateTo) {
            $query->whereHas('stockReleaseNote', function($q) {
                $q->whereBetween('release_date', [$this->dateFrom, $this->dateTo]);
            });
        }

        $result = $query->orderBy('srn_id', 'desc')
                    ->orderBy('id', 'asc')
                    ->get();

        // Debug log to help troubleshoot empty exports
        Log::info('SrnItemsSheet export collection', [
            'count' => $result->count(),
            'srnIds' => $this->srnIds,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'orgId' => $orgId,
            'isSuperAdmin' => $isSuperAdmin
        ]);

        return $result;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'SRN ID',
            'SRN Number',
            'Organization',
            'Branch',
            'Release Type',
            'Item ID',
            'Item Code',
            'Item Name',
            'Category',
            'Release Quantity',
            'Unit Price',
            'Line Total',
            'Batch Number',
            'Expiry Date',
            'Release Date',
            'Notes'
        ];
    }

    /**
     * @param mixed $srnItem
     * @return array
     */
    public function map($srnItem): array
    {
        try {
            return [
                $srnItem->srn_id,
                $srnItem->stockReleaseNote->srn_number ?? 'N/A',
                $srnItem->stockReleaseNote->organization->name ?? 'N/A',
                $srnItem->stockReleaseNote->branch->name ?? 'N/A',
                $srnItem->stockReleaseNote->release_type ?? 'N/A',
                $srnItem->item_id,
                $srnItem->item->item_code ?? 'N/A',
                $srnItem->item->name ?? 'N/A',
                $srnItem->item->category->name ?? 'N/A',
                $srnItem->release_quantity,
                $srnItem->release_price ?? 0,
                $srnItem->line_total ?? 0,
                $srnItem->batch_no ?? 'N/A',
                $srnItem->expiry_date ? $srnItem->expiry_date->format('Y-m-d') : 'N/A',
                $srnItem->stockReleaseNote->release_date ? $srnItem->stockReleaseNote->release_date->format('Y-m-d') : 'N/A',
                $srnItem->notes ?? 'N/A'
            ];
        } catch (\Exception $e) {
            Log::error('SrnItemsSheet mapping error', [
                'item_id' => $srnItem->id ?? 'N/A',
                'srn_id' => $srnItem->srn_id ?? 'N/A',
                'error' => $e->getMessage()
            ]);

            // Return safe values if there's an error
            return [
                $srnItem->srn_id ?? 'N/A',
                'ERROR',
                'ERROR',
                'ERROR',
                'ERROR',
                $srnItem->item_id ?? 'N/A',
                'ERROR',
                'ERROR',
                'ERROR',
                $srnItem->release_quantity ?? 0,
                0,
                0,
                'N/A',
                'N/A',
                'N/A',
                'ERROR'
            ];
        }
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'SRN Items Data';
    }
}
