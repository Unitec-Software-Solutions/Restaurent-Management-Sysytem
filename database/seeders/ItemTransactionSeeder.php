<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;

use App\Models\{User, Organizations, Branch, ItemMaster};
use App\Models\ItemTransaction;
use Illuminate\Support\Str;

class ItemTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $orgIds = Organizations::pluck('id')->toArray();
        $branchIds = Branch::pluck('id')->toArray();
        $itemIds = ItemMaster::pluck('id')->toArray();

        if (empty($userIds) || empty($orgIds) || empty($branchIds) || empty($itemIds)) {
            $this->command->warn('Not enough related data to seed item transactions.');
            return;
        }
        // Seed 10 sample item transactions
        for ($i = 0; $i < 10; $i++) {
            ItemTransaction::create([
                'organization_id' => fake()->randomElement($orgIds),
                'branch_id' => fake()->randomElement($branchIds),
                'inventory_item_id' => fake()->randomElement($itemIds),
                'transaction_type' => fake()->randomElement(['in', 'out', 'transfer']),
                'transfer_to_branch_id' => fake()->randomElement($branchIds),
                'receiver_user_id' => fake()->randomElement($userIds),
                'quantity' => rand(50, 1000),
                'received_quantity' => rand(50, 1000),
                'damaged_quantity' => rand(0, 10),
                'cost_price' => rand(1000, 60000) / 10,
                'unit_price' => rand(100, 1500) / 10,
                'source_id' => rand(1, 10),
                'source_type' => fake()->randomElement(['Order', 'Return']),
                'created_by_user_id' => fake()->randomElement($userIds),
                'notes' => Str::random(20),
                'is_active' => fake()->boolean,
            ]);
        }
    }
}
