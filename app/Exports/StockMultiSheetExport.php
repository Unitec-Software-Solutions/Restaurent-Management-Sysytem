<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StockMultiSheetExport implements WithMultipleSheets
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
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // Stock Levels Sheet
        $sheets[] = new StockLevelsSheet($this->itemIds, $this->branchId, $this->dateFrom, $this->dateTo, $this->filters);

        // Stock Transactions History Sheet
        $sheets[] = new StockTransactionsSheet($this->itemIds, $this->branchId, $this->dateFrom, $this->dateTo, $this->filters);

        return $sheets;
    }
}
