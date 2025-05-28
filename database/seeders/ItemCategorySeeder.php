<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use App\Models\Organizations;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organizations::where('is_active', true)->take(5)->get();

        if ($organizations->isEmpty()) {
            $this->command->error('❌ No active organizations found to seed item categories.');
            return;
        }

        $baseCategories = [
            [
                'name' => 'Main Course',
                'code' => 'MC',
                'description' => 'Entrees and primary dishes',
            ],
            [
                'name' => 'Beverages',
                'code' => 'BV',
                'description' => 'Soft drinks, juices, etc.',
            ],
            [
                'name' => 'Desserts',
                'code' => 'DS',
                'description' => 'Cakes, pastries, and sweets',
            ],
            [
                'name' => 'Buy & sell',
                'code' => 'BS',
                'description' => 'Buy and sell items',
            ],
            [
                'name' => 'Ingredients',
                'code' => 'IG',
                'description' => 'Raw cooking ingredients',
            ],
            [
                'name' => 'Utensils & Packaging',
                'code' => 'UP',
                'description' => 'Cutlery, napkins, takeaway boxes',
            ],
        ];

        foreach ($organizations as $org) {
            foreach ($baseCategories as $category) {
                // Adjust code and name to be unique per organization
                $uniqueCode = $category['code'] . $org->id;
                $uniqueName = $category['name'] . ' - Org ' . $org->id;

                $exists = ItemCategory::where('organization_id', $org->id)
                    ->where(function ($query) use ($category, $uniqueCode) {
                        $query->where('name', $category['name'])
                            ->orWhere('code', $uniqueCode);
                    })
                    ->exists();

                if (!$exists) {
                    ItemCategory::create([
                        'name' => $category['name'],
                        'code' => $uniqueCode,
                        'description' => $category['description'],
                        'is_active' => true,
                        'organization_id' => $org->id,
                    ]);
                }
            }
        }

        $this->command->info("  ✅ Seeded item categories for {$organizations->count()} organizations.");
        $this->command->info("  Total Item Categories in the database: " . ItemCategory::count());
    }
}
