<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use App\Models\ItemMaster;
use App\Models\ItemTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockLevelsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStrictNullComparison
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

        $query = ItemMaster::with(['category', 'organization']);

        // Apply organization filter for non-super admins
        if (!$isSuperAdmin && $orgId) {
            $query->where('organization_id', $orgId);
        }

        // Apply specific item IDs if provided
        if ($this->itemIds) {
            $query->whereIn('id', $this->itemIds);
        }

        // Apply branch filter if provided
        if ($this->branchId) {
            $query->whereHas('transactions', function($q) {
                $q->where('branch_id', $this->branchId);
            });
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

        $items = $query->orderBy('name', 'asc')->get();

        // Calculate current stock for each item
        return $items->map(function ($item) {
            $currentStock = $this->getCurrentStock($item->id, $this->branchId);
            $stockStatus = $this->getStockStatus($currentStock, $item->reorder_level ?? 0);

            // Add calculated fields to the item
            $item->current_stock = $currentStock;
            $item->stock_status = $stockStatus;
            $item->stock_value = $currentStock * ($item->buying_price ?? 0);

            return $item;
        });
    }

    /**
     * Calculate current stock for an item in a specific branch
     */
    protected function getCurrentStock($itemId, $branchId = null)
    {
        $query = ItemTransaction::where('inventory_item_id', $itemId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Apply date filter if provided
        if ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo);
        }

        return $query->sum('quantity') ?? 0;
    }

    /**
     * Determine stock status based on current stock and reorder level
     */
    protected function getStockStatus($currentStock, $reorderLevel)
    {
        if ($currentStock <= 0) {
            return 'Out of Stock';
        } elseif ($currentStock <= $reorderLevel) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Item ID',
            'Item Code',
            'Item Name',
            'Category',
            'Organization',
            'Current Stock',
            'Unit of Measurement',
            'Reorder Level',
            'Stock Status',
            'Buying Price',
            'Selling Price',
            'Stock Value',
            'Is Active',
            'Is Menu Item',
            'Is Perishable',
            'Created Date',
            'Last Updated'
        ];
    }

    /**
     * @param mixed $item
     * @return array
     */
    public function map($item): array
    {
        return [
            $item->id,
            $item->item_code,
            $item->name,
            $item->category->name ?? 'N/A',
            $item->organization->name ?? 'N/A',
            $item->current_stock ?? 0,
            $item->unit_of_measurement ?? 'N/A',
            $item->reorder_level ?? 0,
            $item->stock_status,
            $item->buying_price ?? 0,
            $item->selling_price ?? 0,
            $item->stock_value ?? 0,
            $item->is_active ? 'Yes' : 'No',
            $item->is_menu_item ? 'Yes' : 'No',
            $item->is_perishable ? 'Yes' : 'No',
            $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : 'N/A',
            $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : 'N/A'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Stock Levels';
    }
}
