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
        // Add cost_price to menu_items
        if (Schema::hasTable('menu_items') && !Schema::hasColumn('menu_items', 'cost_price')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->decimal('cost_price', 10, 2)->nullable()->after('price');
            });
        }

        // Add phone to users (if not using phone_number)
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone')->nullable()->after('email');
            });
        }

        // Add supplier_type to suppliers
        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'supplier_type')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('supplier_type')->default('general')->after('name');
            });
        }

        // Fix item_categories to make code nullable
        if (Schema::hasTable('item_categories') && Schema::hasColumn('item_categories', 'code')) {
            Schema::table('item_categories', function (Blueprint $table) {
                $table->string('code')->nullable()->change();
            });
        }

        // Add missing fields to kitchen_stations if needed
        if (Schema::hasTable('kitchen_stations')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                if (!Schema::hasColumn('kitchen_stations', 'station_type')) {
                    $table->string('station_type')->default('standard')->after('type');
                }
                if (!Schema::hasColumn('kitchen_stations', 'priority_level')) {
                    $table->integer('priority_level')->default(1)->after('order_priority');
                }
                if (!Schema::hasColumn('kitchen_stations', 'max_concurrent_orders')) {
                    $table->integer('max_concurrent_orders')->default(5)->after('max_capacity');
                }
                if (!Schema::hasColumn('kitchen_stations', 'current_orders')) {
                    $table->integer('current_orders')->default(0)->after('max_concurrent_orders');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('menu_items') && Schema::hasColumn('menu_items', 'cost_price')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropColumn('cost_price');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }

        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'supplier_type')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('supplier_type');
            });
        }

        if (Schema::hasTable('kitchen_stations')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                if (Schema::hasColumn('kitchen_stations', 'station_type')) {
                    $table->dropColumn('station_type');
                }
                if (Schema::hasColumn('kitchen_stations', 'priority_level')) {
                    $table->dropColumn('priority_level');
                }
                if (Schema::hasColumn('kitchen_stations', 'max_concurrent_orders')) {
                    $table->dropColumn('max_concurrent_orders');
                }
                if (Schema::hasColumn('kitchen_stations', 'current_orders')) {
                    $table->dropColumn('current_orders');
                }
            });
        }
    }
};
