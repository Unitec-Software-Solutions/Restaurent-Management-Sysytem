<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists, if not create it, if it does, modify it
        if (!Schema::hasTable('menu_items')) {
            Schema::create('menu_items', function (Blueprint $table) {
                $table->id();
                
                // Organization & Branch references
                $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
                $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
                
                // Category reference
                $table->foreignId('menu_category_id')->constrained('menu_categories')->onDelete('cascade');
                
                // Optional reference to item master (if created from inventory item)
                $table->foreignId('item_master_id')->nullable()->constrained('item_master')->onDelete('set null');
                
                // Basic Information
                $table->string('name');
                $table->string('unicode_name')->nullable();
                $table->text('description')->nullable();
                $table->string('item_code')->nullable();
                
                // Pricing
                $table->decimal('price', 10, 2);
                $table->decimal('cost_price', 10, 2)->nullable();
                $table->string('currency', 3)->default('LKR');
                
                // Promotion pricing
                $table->decimal('promotion_price', 10, 2)->nullable();
                $table->timestamp('promotion_start')->nullable();
                $table->timestamp('promotion_end')->nullable();
                
                // Display & Media
                $table->string('image_path')->nullable();
                $table->string('image_url')->nullable();
                $table->integer('display_order')->default(0);
                $table->integer('sort_order')->default(0);
                
                // Availability & Status
                $table->boolean('is_available')->default(true);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_featured')->default(false);
                
                // Kitchen & Preparation
                $table->boolean('requires_preparation')->default(true);
                $table->integer('preparation_time')->nullable(); // in minutes
                $table->string('station')->nullable();
                $table->foreignId('kitchen_station_id')->nullable()->constrained('kitchen_stations')->onDelete('set null');
                
                // Dietary Information
                $table->boolean('is_vegetarian')->default(false);
                $table->boolean('is_vegan')->default(false);
                $table->boolean('is_spicy')->default(false);
                $table->string('spice_level')->default('mild'); // mild, medium, hot, very_hot
                $table->boolean('contains_alcohol')->default(false);
                
                // Nutritional Information
                $table->integer('calories')->nullable();
                $table->json('allergens')->nullable(); // Array of allergen types
                $table->json('allergen_info')->nullable(); // Detailed allergen information
                $table->json('nutritional_info')->nullable(); // Detailed nutritional facts
                $table->text('ingredients')->nullable();
                
                // Menu Item Type
                $table->tinyInteger('type')->default(2); // 1=buy_sell, 2=kot (kitchen order ticket)
                
                // Additional Information
                $table->text('special_instructions')->nullable();
                $table->json('customization_options')->nullable(); // Available customizations
                $table->text('notes')->nullable();
                
                $table->softDeletes();
                $table->timestamps();
                
                // Indexes for performance
                $table->index(['organization_id', 'is_active']);
                $table->index(['branch_id', 'is_available']);
                $table->index(['menu_category_id', 'display_order']);
                $table->index(['is_featured', 'is_available']);
                $table->index(['requires_preparation', 'station']);
                $table->index('type');
                $table->index('sort_order');
            });
        } else {
            // Table exists, add any missing columns
            Schema::table('menu_items', function (Blueprint $table) {
                // Add type column if it doesn't exist
                if (!Schema::hasColumn('menu_items', 'type')) {
                    $table->tinyInteger('type')->default(2)->after('ingredients'); // 1=buy_sell, 2=kot
                    $table->index('type');
                }
                
                // Add other missing columns that might be needed for KOT functionality
                if (!Schema::hasColumn('menu_items', 'unicode_name')) {
                    $table->string('unicode_name')->nullable()->after('name');
                }
                
                if (!Schema::hasColumn('menu_items', 'item_code')) {
                    $table->string('item_code')->nullable()->after('description');
                }
                
                if (!Schema::hasColumn('menu_items', 'currency')) {
                    $table->string('currency', 3)->default('LKR')->after('cost_price');
                }
                
                if (!Schema::hasColumn('menu_items', 'special_instructions')) {
                    $table->text('special_instructions')->nullable()->after('ingredients');
                }
                
                if (!Schema::hasColumn('menu_items', 'customization_options')) {
                    $table->json('customization_options')->nullable()->after('special_instructions');
                }
                
                if (!Schema::hasColumn('menu_items', 'notes')) {
                    $table->text('notes')->nullable()->after('customization_options');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
