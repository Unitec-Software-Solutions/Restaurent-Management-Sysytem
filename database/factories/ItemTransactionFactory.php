<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ItemTransaction;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\ItemMaster;
use App\Models\User;
use App\Models\Supplier;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemTransaction>
 */
class ItemTransactionFactory extends Factory
{
    protected $model = ItemTransaction::class;

    /**
     * Define the model's default state for Laravel + PostgreSQL + Tailwind CSS stack.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $receivedQuantity = $quantity - $this->faker->randomFloat(2, 0, $quantity * 0.1); // Up to 10% damage
        $damagedQuantity = $quantity - $receivedQuantity;
        $costPrice = $this->faker->randomFloat(4, 1, 100);
        $unitPrice = $costPrice * $this->faker->randomFloat(2, 1.2, 2.5); // 20-150% markup
        
        return [
            'organization_id' => Organization::factory(),
            'branch_id' => function (array $attributes) {
                return Branch::where('organization_id', $attributes['organization_id'])->first()?->id
                       ?? Branch::factory()->create(['organization_id' => $attributes['organization_id']])->id;
            },
            'inventory_item_id' => function (array $attributes) {
                return ItemMaster::where('organization_id', $attributes['organization_id'])->first()?->id
                       ?? ItemMaster::factory()->create(['organization_id' => $attributes['organization_id']])->id;
            },
            'transaction_type' => $this->faker->randomElement([
                'opening_stock', 'purchase_order', 'sales_order', 'adjustment',
                'transfer_in', 'transfer_out', 'grn_stock_in', 'gtn_stock_out'
            ]),
            'incoming_branch_id' => null,
            'receiver_user_id' => function (array $attributes) {
                return User::where('organization_id', $attributes['organization_id'])->first()?->id;
            },
            'quantity' => $quantity,
            'received_quantity' => $receivedQuantity,
            'damaged_quantity' => $damagedQuantity,
            'unit_of_measurement' => $this->faker->randomElement(['kg', 'g', 'liter', 'ml', 'piece', 'packet']),
            'transaction_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'transaction_time' => $this->faker->time('H:i:s'),
            'cost_price' => $costPrice,
            'unit_price' => $unitPrice,
            'total_amount' => $quantity * $costPrice,
            'balance_after_transaction' => $this->faker->randomFloat(2, 0, 500),
            'reference_number' => null, // Will be auto-generated
            'supplier_id' => function (array $attributes) {
                return Supplier::where('organization_id', $attributes['organization_id'])->first()?->id;
            },
            'order_id' => null,
            'batch_number' => $this->faker->optional()->bothify('BATCH-####'),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'source_id' => $this->faker->optional()->numerify('SRC-####'),
            'source_type' => $this->faker->randomElement(['Manual', 'PO', 'GRN', 'Adjustment', 'Transfer']),
            'created_by_user_id' => function (array $attributes) {
                return User::where('organization_id', $attributes['organization_id'])->first()?->id ?? 1;
            },
            'approved_by' => function (array $attributes) {
                return $this->faker->optional()->randomElement([
                    User::where('organization_id', $attributes['organization_id'])->first()?->id
                ]);
            },
            'approval_date' => function (array $attributes) {
                return $attributes['approved_by'] ? $this->faker->dateTimeBetween('-7 days', 'now') : null;
            },
            'verified_by' => null,
            'notes' => $this->faker->optional()->sentence(),
            'metadata' => [
                'factory_generated' => true,
                'created_at' => now()->toISOString(),
                'batch_info' => $this->faker->optional()->words(3, true),
                'quality_check' => $this->faker->boolean(80),
                'temperature' => $this->faker->optional()->randomFloat(1, -5, 25),
            ],
            'is_active' => true,
        ];
    }

    /**
     * Create opening stock transaction
     */
    public function openingStock(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'transaction_type' => 'opening_stock',
                'quantity' => $this->faker->numberBetween(50, 200),
                'received_quantity' => function (array $attributes) {
                    return $attributes['quantity'];
                },
                'damaged_quantity' => 0,
                'source_type' => 'Manual',
                'notes' => 'Opening stock entry',
                'metadata' => [
                    'type' => 'opening_stock',
                    'created_via' => 'factory',
                    'audit_required' => false,
                ],
            ];
        });
    }

    /**
     * Create purchase transaction
     */
    public function purchase(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'transaction_type' => 'purchase_order',
                'source_type' => 'PurchaseOrder',
                'notes' => 'Stock purchased from supplier',
                'metadata' => [
                    'type' => 'purchase',
                    'po_required' => true,
                    'supplier_verified' => true,
                ],
            ];
        });
    }

    /**
     * Create sales transaction
     */
    public function sales(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = abs($attributes['quantity']) * -1; // Make negative for stock out
            
            return [
                'transaction_type' => 'sales_order',
                'quantity' => $quantity,
                'received_quantity' => $quantity,
                'damaged_quantity' => 0,
                'source_type' => 'Order',
                'notes' => 'Stock sold to customer',
                'metadata' => [
                    'type' => 'sales',
                    'customer_order' => true,
                    'revenue_impact' => true,
                ],
            ];
        });
    }

    /**
     * Create adjustment transaction
     */
    public function adjustment(): static
    {
        return $this->state(function (array $attributes) {
            $isPositive = $this->faker->boolean(50);
            $quantity = $this->faker->randomFloat(2, 1, 50);
            
            return [
                'transaction_type' => 'adjustment',
                'quantity' => $isPositive ? $quantity : -$quantity,
                'received_quantity' => $isPositive ? $quantity : -$quantity,
                'damaged_quantity' => 0,
                'source_type' => 'Manual',
                'notes' => $isPositive ? 'Stock adjustment - increase' : 'Stock adjustment - decrease',
                'metadata' => [
                    'type' => 'adjustment',
                    'reason' => $this->faker->randomElement(['Count correction', 'Damage repair', 'Found stock', 'Spoilage']),
                    'requires_approval' => true,
                ],
            ];
        });
    }

    /**
     * Create transaction for specific item
     */
    public function forItem(ItemMaster $item): static
    {
        return $this->state(function (array $attributes) use ($item) {
            return [
                'organization_id' => $item->organization_id,
                'branch_id' => $item->branch_id,
                'inventory_item_id' => $item->id,
                'unit_of_measurement' => $item->unit_of_measurement,
                'cost_price' => $item->buying_price ?? $item->cost_price ?? 10,
                'unit_price' => $item->selling_price ?? 15,
            ];
        });
    }

    /**
     * Create transaction for specific branch
     */
    public function forBranch(Branch $branch): static
    {
        return $this->state(function (array $attributes) use ($branch) {
            return [
                'organization_id' => $branch->organization_id,
                'branch_id' => $branch->id,
            ];
        });
    }
}