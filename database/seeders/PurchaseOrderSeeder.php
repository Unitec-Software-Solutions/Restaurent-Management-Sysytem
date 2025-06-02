<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Supplier;
use App\Models\ItemMaster;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run()
    {
        $startDate = Carbon::now()->subMonths(3);
        
        // For each organization
        for ($orgId = 1; $orgId <= 5; $orgId++) {
            $supplier = Supplier::where('organization_id', $orgId)->get();
            $item = ItemMaster::where('organization_id', $orgId)->get();
            $branch = \App\Models\Branch::where('organization_id', $orgId)->get();

            // Get valid user for this organization
            $user = User::where('organization_id', $orgId)->first();
            
            if (!$user) {
                $this->command->warn("  ⚠️  No user found for organization $orgId - skipping PO creation");
                continue;
            }

            // Skip if no item available
            if ($item->isEmpty()) {
                $this->command->warn("  ⚠️  No item found for organization $orgId - skipping PO creation");
                continue;
            }
        }
    }
}