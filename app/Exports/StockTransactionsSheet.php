<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\ItemTransaction;
use Illuminate\Support\Facades\Auth;

class StockTransactionsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStrictNullComparison
{
    protected $itemIds;
    protected $branchId;
    protected $dateFrom;
    protected $dateTo;
    protected $filters;

    public function __construct($itemIds = null, $branchId = null, $dateFrom = null, $dateTo = null, $filters = [])
    {
        $this->itemIds = $itemIds;
        $this->branchId = $branchId;
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

        $query = ItemTransaction::with([
            'item.category',
            'branch'
        ]);

        // Apply organization filter through item relationship
        if (!$isSuperAdmin && $orgId) {
            $query->whereHas('item', function($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            });
        }

        // Apply specific item IDs if provided
        if ($this->itemIds) {
            $query->whereIn('inventory_item_id', $this->itemIds);
        }

        // Apply branch filter if provided
        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        // Apply date range
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);
        } elseif ($this->dateFrom) {
            $query->where('created_at', '>=', $this->dateFrom);
        } elseif ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo);
        }

        // Apply additional filters
        foreach ($this->filters as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        return $query->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Transaction ID',
            'Item ID',
            'Item Code',
            'Item Name',
            'Category',
            'Branch',
            'Transaction Type',
            'Quantity',
            'Signed Quantity',
            'Unit Price',
            'Total Value',
            'Transaction Date',
            'Reference Type',
            'Reference ID',
            'Reference Number',
            'Batch Number',
            'Expiry Date',
            'Created By',
            'Created Date',
            'Notes'
        ];
    }

    /**
     * @param mixed $transaction
     * @return array
     */
    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->inventory_item_id,
            $transaction->item->item_code ?? 'N/A',
            $transaction->item->name ?? 'N/A',
            $transaction->item->category->name ?? 'N/A',
            $transaction->branch->name ?? 'N/A',
            $transaction->transaction_type,
            $transaction->quantity,
            $transaction->quantity, // Signed quantity same as quantity in this model
            $transaction->unit_price ?? 0,
            $transaction->total_amount ?? 0,
            $transaction->created_at ? $transaction->created_at->format('Y-m-d') : 'N/A',
            $transaction->reference_type ?? 'N/A',
            $transaction->reference_id ?? 'N/A',
            $transaction->reference_number ?? 'N/A',
            $transaction->batch_number ?? 'N/A',
            $transaction->expiry_date ? $transaction->expiry_date->format('Y-m-d') : 'N/A',
            'N/A', // Created By - relationship not available
            $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : 'N/A',
            $transaction->notes ?? 'N/A'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Stock Transactions';
    }
}
