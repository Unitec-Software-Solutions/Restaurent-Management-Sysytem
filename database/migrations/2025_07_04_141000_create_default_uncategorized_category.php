<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create a default "Uncategorized" category for each organization
        $organizations = DB::table('organizations')->get();
        
        foreach ($organizations as $organization) {
            // Check if "Uncategorized" category already exists
            $existingCategory = DB::table('menu_categories')
                ->where('organization_id', $organization->id)
                ->where('name', 'Uncategorized')
                ->first();
                
            if (!$existingCategory) {
                DB::table('menu_categories')->insert([
                    'organization_id' => $organization->id,
                    'name' => 'Uncategorized',
                    'unicode_name' => 'Uncategorized',
                    'description' => 'Default category for menu items without specific category',
                    'sort_order' => 999,
                    'display_order' => 999,
                    'is_active' => true,
                    'is_featured' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove default "Uncategorized" categories
        DB::table('menu_categories')
            ->where('name', 'Uncategorized')
            ->where('description', 'Default category for menu items without specific category')
            ->delete();
    }
};
