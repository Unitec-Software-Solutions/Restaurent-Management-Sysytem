<?php

namespace App\Exports;

use Illuminate\Support\Collection;

class GenericExport
{
    protected $data;
    protected $headers;
    protected $mappingCallback;

    public function __construct($data, array $headers = [], callable $mappingCallback = null)
    {
        $this->data = $data instanceof Collection ? $data : collect($data);
        $this->headers = $headers;
        $this->mappingCallback = $mappingCallback;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        if (!empty($this->headers)) {
            return $this->headers;
        }

        // Generate headers from first item if no headers provided
        if ($this->data->isNotEmpty()) {
            $firstItem = $this->data->first();
            if (is_array($firstItem)) {
                return array_keys($firstItem);
            } elseif (is_object($firstItem)) {
                return array_keys($firstItem->toArray());
            }
        }

        return [];
    }

    /**
     * Format row data for export
     */
    protected function formatRowData(array $data): array
    {
        foreach ($data as $key => $value) {
            // Format dates
            if ($this->isDateField($key) && $value) {
                $data[$key] = \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
            }
            
            // Format booleans
            if (is_bool($value)) {
                $data[$key] = $value ? 'Yes' : 'No';
            }
            
            // Format currency fields
            if ($this->isCurrencyField($key) && is_numeric($value)) {
                $data[$key] = number_format($value, 2);
            }
            
            // Handle null values
            if (is_null($value)) {
                $data[$key] = '';
            }
        }

        return $data;
    }

    /**
     * Check if field is a date field
     */
    protected function isDateField(string $key): bool
    {
        $dateFields = ['created_at', 'updated_at', 'deleted_at', 'date', 'time', 'joined_date', 'order_date', 'reservation_date'];
        return in_array($key, $dateFields) || str_contains($key, '_date') || str_contains($key, '_at');
    }

    /**
     * Check if field is a currency field
     */
    protected function isCurrencyField(string $key): bool
    {
        $currencyFields = ['price', 'amount', 'total', 'cost', 'salary', 'fee', 'payment'];
        return in_array($key, $currencyFields) || str_contains($key, '_price') || str_contains($key, '_cost') || str_contains($key, '_amount');
    }
}
