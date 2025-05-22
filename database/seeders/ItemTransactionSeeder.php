<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Organizations, Branch, ItemMaster, ItemTransaction};
use Illuminate\Support\Str;

class ItemTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users    = User::pluck('id')->toArray();
        $orgs     = Organizations::pluck('id')->toArray();
        $branches = Branch::pluck('id')->toArray();
        $items    = ItemMaster::pluck('id')->toArray();

        if (empty($users) || empty($orgs) || empty($branches) || empty($items)) {
            $this->command->warn('Missing related data: cannot seed item transactions.');
            return;
        }

        $inventory = [];

        // Seed initial stock
        foreach ($branches as $branchId) {
            foreach ($items as $itemId) {
                $initial = rand(100, 500);
                $inventory[$branchId][$itemId] = $initial;

                ItemTransaction::create([
                    'organization_id'        => $orgs[array_rand($orgs)],
                    'branch_id'              => $branchId,
                    'inventory_item_id'      => $itemId,
                    'transaction_type'       => 'purchase_order',
                    'transfer_to_branch_id'  => null,
                    'receiver_user_id'       => $users[array_rand($users)],
                    'quantity'               => $initial,
                    'received_quantity'      => $initial,
                    'damaged_quantity'       => 0,
                    'cost_price'             => rand(500, 2000) / 10,
                    'unit_price'             => rand(100, 1500) / 10,
                    'source_id'              => null,
                    'source_type'            => 'InitialStock',
                    'created_by_user_id'     => $users[array_rand($users)],
                    'notes'                  => Str::random(15),
                    'is_active'              => true,
                ]);
            }
        }

        $types = ['purchase_order', 'sales_order', 'transfer', 'return', 'adjustment', 'write_off', 'audit'];

        for ($i = 0; $i < 30; $i++) {
            $type      = $types[array_rand($types)];
            $branchId  = $branches[array_rand($branches)];
            $itemId    = $items[array_rand($items)];
            $maxStock  = $inventory[$branchId][$itemId] ?? 0;
            $qty       = 0;
            $received  = 0;
            $target    = null;

            switch ($type) {
                case 'purchase_order':
                    $qty = rand(20, 100);
                    $received = $qty - rand(0, 5);
                    $inventory[$branchId][$itemId] += $received;
                    break;

                case 'sales_order':
                    if ($maxStock < 5) {
                        $i--;
                        continue 2;
                    }
                    $qty = rand(5, min(50, $maxStock));
                    $received = $qty;
                    $inventory[$branchId][$itemId] -= $qty;
                    break;

                case 'transfer':
                    $target = $branches[array_rand($branches)];
                    if ($target === $branchId || $maxStock < 10) {
                        $i--;
                        continue 2;
                    }
                    $qty = rand(10, min(100, $maxStock));
                    $received = $qty;
                    $inventory[$branchId][$itemId] -= $qty;
                    $inventory[$target][$itemId] += $qty;
                    break;

                case 'return':
                    $qty = rand(1, 30);
                    $received = $qty;
                    $inventory[$branchId][$itemId] += $received;
                    break;

                case 'adjustment':
                    $delta = rand(-20, 20);
                    if ($delta === 0) {
                        $i--;
                        continue 2;
                    }
                    $qty = abs($delta);
                    $received = $delta > 0 ? $qty : 0;
                    $inventory[$branchId][$itemId] += $delta;
                    break;

                case 'write_off':
                    if ($maxStock < 1) {
                        $i--;
                        continue 2;
                    }
                    $qty = rand(1, min(10, $maxStock));
                    $received = 0;
                    $inventory[$branchId][$itemId] -= $qty;
                    break;

                case 'audit':
                    $qty = $inventory[$branchId][$itemId];
                    $received = $qty;
                    break;

                default:
                    continue 2;
            }

            ItemTransaction::create([
                'organization_id'        => $orgs[array_rand($orgs)],
                'branch_id'              => $branchId,
                'inventory_item_id'      => $itemId,
                'transaction_type'       => $type,
                'transfer_to_branch_id'  => $type === 'transfer' ? $target : null,
                'receiver_user_id'       => $users[array_rand($users)],
                'quantity'               => $qty,
                'received_quantity'      => $received,
                'damaged_quantity'       => $type === 'purchase_order' ? rand(0, 3) : 0,
                'cost_price'             => rand(500, 2000) / 10,
                'unit_price'             => rand(100, 1500) / 10,
                'source_id'              => $i + 1,
                'source_type'            => Str::studly($type),
                'created_by_user_id'     => $users[array_rand($users)],
                'notes'                  => Str::random(20),
                'is_active'              => true,
            ]);
        }
        $this->command->info('  Total Item transactions in the database : ' . ItemTransaction::count());
        $this->command->info('  ✅ Item transactions seeded successfully.');
    }
}
