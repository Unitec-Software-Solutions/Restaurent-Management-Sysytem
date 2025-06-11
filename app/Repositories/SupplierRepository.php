<?php

namespace App\Repositories;

use App\Models\Supplier;

class SupplierRepository
{
    public function getRecentTransactions(Supplier $supplier, $limit = 5)
    {
        return $supplier->transactions()
            ->where('organization_id', $supplier->organization_id)
            ->latest()
            ->take($limit)
            ->get();
    }
 
}