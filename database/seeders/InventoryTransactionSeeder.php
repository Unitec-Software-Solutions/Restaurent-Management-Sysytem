<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Branch;
use Carbon\Carbon;

class InventoryTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = InventoryItem::all();
        $users = User::all();
        $suppliers = Supplier::all();
        $branches = Branch::all();
        
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run the user seeder first.');
            return;
        }
        
        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please run the branch seeder first.');
            return;
        }
        
        $transactionTypes = ['purchase', 'usage', 'adjustment', 'wastage'];
        $startDate = Carbon::now()->subDays(90); // 3 months of history
        
        $transactionCount = 0;
        
        foreach ($items as $item) {
            // Each item will have 5-15 random transactions
            $numTransactions = rand(5, 15);
            
            for ($i = 0; $i < $numTransactions; $i++) {
                $type = $transactionTypes[array_rand($transactionTypes)];
                $transactionDate = Carbon::instance($startDate)->addDays(rand(0, 90))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                
                // Generate appropriate quantity based on transaction type and item
                $quantity = 0;
                switch ($type) {
                    case 'purchase':
                        // Purchases are generally in larger quantities
                        switch ($item->unit_of_measurement) {
                            case 'kg':
                                $quantity = rand(5, 25);
                                break;
                            case 'ltr':
                                $quantity = rand(5, 20);
                                break;
                            case 'pcs':
                            case 'bottle':
                            case 'can':
                                $quantity = rand(24, 100);
                                break;
                            default:
                                $quantity = rand(5, 30);
                        }
                        break;
                        
                    case 'usage':
                        // Usage is generally in smaller quantities
                        switch ($item->unit_of_measurement) {
                            case 'kg':
                                $quantity = rand(1, 10) / 2; // 0.5 to 5 kg
                                break;
                            case 'ltr':
                                $quantity = rand(1, 10) / 2; // 0.5 to 5 liters
                                break;
                            case 'pcs':
                            case 'bottle':
                            case 'can':
                                $quantity = rand(1, 24);
                                break;
                            default:
                                $quantity = rand(1, 10);
                        }
                        break;
                        
                    case 'adjustment':
                    case 'wastage':
                        // Small quantity adjustments or wastage
                        switch ($item->unit_of_measurement) {
                            case 'kg':
                            case 'ltr':
                                $quantity = rand(1, 5) / 2; // 0.5 to 2.5 units
                                break;
                            case 'pcs':
                            case 'bottle':
                            case 'can':
                                $quantity = rand(1, 5);
                                break;
                            default:
                                $quantity = rand(1, 3);
                        }
                        break;
                }
                
                // Unit price only for purchases
                $unitPrice = null;
                if ($type === 'purchase') {
                    // Generate reasonable prices based on unit type
                    switch ($item->unit_of_measurement) {
                        case 'kg':
                            $unitPrice = rand(200, 1500) / 100; // $2.00 to $15.00 per kg
                            break;
                        case 'ltr':
                            $unitPrice = rand(100, 800) / 100; // $1.00 to $8.00 per liter
                            break;
                        case 'pcs':
                            $unitPrice = rand(50, 500) / 100; // $0.50 to $5.00 per piece
                            break;
                        case 'bottle':
                            $unitPrice = rand(100, 3000) / 100; // $1.00 to $30.00 per bottle
                            break;
                        case 'can':
                            $unitPrice = rand(100, 300) / 100; // $1.00 to $3.00 per can
                            break;
                        default:
                            $unitPrice = rand(100, 1000) / 100; // $1.00 to $10.00 default
                    }
                    
                    // Adjust price based on category
                    if (str_contains($item->category->name, 'Beverages')) {
                        $unitPrice *= 1.2; // Higher price for beverages
                    } elseif (str_contains($item->category->name, 'Dairy')) {
                        $unitPrice *= 1.5; // Higher price for dairy
                    }
                }
                
                // Source info only for purchases
                $sourceId = null;
                $sourceType = null;
                if ($type === 'purchase' && !$suppliers->isEmpty()) {
                    $sourceId = $suppliers->random()->id;
                    $sourceType = 'Supplier';
                }
                
                // Transaction notes
                $notes = null;
                switch ($type) {
                    case 'purchase':
                        $notes = 'Regular inventory purchase';
                        break;
                    case 'usage':
                        $notes = 'Used for kitchen production';
                        break;
                    case 'adjustment':
                        $notes = rand(0, 1) ? 'Inventory count correction' : 'Damaged items adjustment';
                        break;
                    case 'wastage':
                        $notes = rand(0, 1) ? 'Expired product' : 'Quality issues';
                        break;
                }
                
                // Create transaction with a valid branch ID
                InventoryTransaction::create([
                    'branch_id' => $branches->random()->id, // Use an actual branch from the database
                    'inventory_item_id' => $item->id,
                    'transaction_type' => $type,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'source_id' => $sourceId,
                    'source_type' => $sourceType,
                    'user_id' => $users->random()->id,
                    'notes' => $notes,
                    'is_active' => true,
                    'created_at' => $transactionDate,
                    'updated_at' => $transactionDate,
                ]);
                
                $transactionCount++;
            }
        }

        $this->command->info("Inventory transactions seeded successfully! Created {$transactionCount} transactions.");
    }
} 