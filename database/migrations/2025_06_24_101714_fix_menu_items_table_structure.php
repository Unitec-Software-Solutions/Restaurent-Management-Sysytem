<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations following UI/UX guidelines.
     */
    public function up(): void
    {
        // Check if menu_items table exists, if not create it
        if (!Schema::hasTable('menu_items')) {
            Schema::create('menu_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->onDelete('cascade');
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
                $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
                $table->foreignId('item_masters_id')->nullable()->constrained('item_masters')->onDelete('set null');
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2);
                $table->decimal('promotion_price', 10, 2)->nullable();
                $table->datetime('promotion_start')->nullable();
                $table->datetime('promotion_end')->nullable();
                $table->string('image_path')->nullable();
                $table->integer('display_order')->default(0);
                $table->boolean('is_available')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->boolean('requires_preparation')->default(true);
                $table->integer('preparation_time')->nullable();
                $table->string('station')->default('kitchen');
                $table->boolean('is_vegetarian')->default(false);
                $table->boolean('is_spicy')->default(false);
                $table->boolean('contains_alcohol')->default(false);
                $table->json('allergens')->nullable();
                $table->integer('calories')->nullable();
                $table->text('ingredients')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
                
                // Add indexes for performance
                $table->index(['organization_id', 'is_available']);
                $table->index(['branch_id', 'is_available']);
                $table->index(['menu_category_id', 'display_order']);
                $table->index(['is_featured', 'is_available']);
            });
        } else {
            // Add missing columns to existing table
            Schema::table('menu_items', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('menu_items');
                
                // Add missing columns
                $columnsToAdd = [
                    'menu_category_id' => function($table) {
                        if (Schema::hasTable('menu_categories')) {
                            $table->foreignId('menu_category_id')->nullable()->after('id')->constrained('menu_categories')->onDelete('cascade');
                        } else {
                            $table->unsignedBigInteger('menu_category_id')->nullable()->after('id');
                        }
                    },
                    'organization_id' => function($table) {
                        $table->foreignId('organization_id')->nullable()->after('menu_category_id')->constrained('organizations')->onDelete('cascade');
                    },
                    'branch_id' => function($table) {
                        $table->foreignId('branch_id')->nullable()->after('organization_id')->constrained('branches')->onDelete('cascade');
                    },
                    'item_masters_id' => function($table) {
                        if (Schema::hasTable('item_masters')) {
                            $table->foreignId('item_masters_id')->nullable()->after('branch_id')->constrained('item_masters')->onDelete('set null');
                        } else {
                            $table->unsignedBigInteger('item_masters_id')->nullable()->after('branch_id');
                        }
                    },
                    'promotion_price' => function($table) {
                        $table->decimal('promotion_price', 10, 2)->nullable()->after('price');
                    },
                    'promotion_start' => function($table) {
                        $table->datetime('promotion_start')->nullable()->after('promotion_price');
                    },
                    'promotion_end' => function($table) {
                        $table->datetime('promotion_end')->nullable()->after('promotion_start');
                    },
                    'image_path' => function($table) {
                        $table->string('image_path')->nullable()->after('promotion_end');
                    },
                    'display_order' => function($table) {
                        $table->integer('display_order')->default(0)->after('image_path');
                    },
                    'is_featured' => function($table) {
                        $table->boolean('is_featured')->default(false)->after('is_available');
                    },
                    'requires_preparation' => function($table) {
                        $table->boolean('requires_preparation')->default(true)->after('is_featured');
                    },
                    'preparation_time' => function($table) {
                        $table->integer('preparation_time')->nullable()->after('requires_preparation');
                    },
                    'station' => function($table) {
                        $table->string('station')->default('kitchen')->after('preparation_time');
                    },
                    'is_vegetarian' => function($table) {
                        $table->boolean('is_vegetarian')->default(false)->after('station');
                    },
                    'is_spicy' => function($table) {
                        $table->boolean('is_spicy')->default(false)->after('is_vegetarian');
                    },
                    'contains_alcohol' => function($table) {
                        $table->boolean('contains_alcohol')->default(false)->after('is_spicy');
                    },
                    'allergens' => function($table) {
                        $table->json('allergens')->nullable()->after('contains_alcohol');
                    },
                    'calories' => function($table) {
                        $table->integer('calories')->nullable()->after('allergens');
                    },
                    'ingredients' => function($table) {
                        $table->text('ingredients')->nullable()->after('calories');
                    },
                ];
                
                foreach ($columnsToAdd as $columnName => $columnDefinition) {
                    if (!in_array($columnName, $existingColumns)) {
                        $columnDefinition($table);
                    }
                }
                
                // Add soft deletes if not exists
                if (!in_array('deleted_at', $existingColumns)) {
                    $table->softDeletes();
                }
            });
            
            // Add indexes for performance
            try {
                Schema::table('menu_items', function (Blueprint $table) {
                    $table->index(['organization_id', 'is_available'], 'menu_items_org_available_idx');
                    $table->index(['branch_id', 'is_available'], 'menu_items_branch_available_idx');
                    $table->index(['menu_category_id', 'display_order'], 'menu_items_category_order_idx');
                    $table->index(['is_featured', 'is_available'], 'menu_items_featured_idx');
                });
            } catch (\Exception $e) {
                // Indexes might already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop the table if it was created by this migration
        // For safety, we won't drop existing tables
        if (Schema::hasTable('menu_items')) {
            try {
                Schema::table('menu_items', function (Blueprint $table) {
                    $table->dropIndex('menu_items_org_available_idx');
                    $table->dropIndex('menu_items_branch_available_idx');
                    $table->dropIndex('menu_items_category_order_idx');
                    $table->dropIndex('menu_items_featured_idx');
                });
            } catch (\Exception $e) {
                // Indexes might not exist
            }
        }
    }
};
