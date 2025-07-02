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
        // Add organization_id to kitchen_stations table
        if (Schema::hasTable('kitchen_stations') && !Schema::hasColumn('kitchen_stations', 'organization_id')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('branch_id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            });
            
            // Update existing kitchen_stations with their branch's organization_id
            DB::statement('UPDATE kitchen_stations SET organization_id = (SELECT organization_id FROM branches WHERE branches.id = kitchen_stations.branch_id) WHERE organization_id IS NULL');
        }

        // Add supplier_id to item_masters table
        if (Schema::hasTable('item_masters') && !Schema::hasColumn('item_masters', 'supplier_id')) {
            Schema::table('item_masters', function (Blueprint $table) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('organization_id');
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            });
        }

        // Add spice_level to menu_items table
        if (Schema::hasTable('menu_items') && !Schema::hasColumn('menu_items', 'spice_level')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->string('spice_level')->default('mild')->after('calories');
            });
        }

        // Add dietary_info to menu_items table
        if (Schema::hasTable('menu_items') && !Schema::hasColumn('menu_items', 'dietary_info')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->json('dietary_info')->nullable()->after('spice_level');
            });
        }

        // Add allergen_info to menu_items table
        if (Schema::hasTable('menu_items') && !Schema::hasColumn('menu_items', 'allergen_info')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->json('allergen_info')->nullable()->after('dietary_info');
            });
        }

        // Add ingredients to menu_items table
        if (Schema::hasTable('menu_items') && !Schema::hasColumn('menu_items', 'ingredients')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->json('ingredients')->nullable()->after('allergen_info');
            });
        }

        // Add image_url to menu_items table
        if (Schema::hasTable('menu_items') && !Schema::hasColumn('menu_items', 'image_url')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->string('image_url')->nullable()->after('ingredients');
            });
        }

        // Add featured to menu_items table
        if (Schema::hasTable('menu_items') && !Schema::hasColumn('menu_items', 'featured')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->boolean('featured')->default(false)->after('image_url');
            });
        }

        // Add guest_session_id to users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'guest_session_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('guest_session_id')->nullable()->after('email_verified_at');
            });
        }

        // Add other missing columns to users table for guest functionality
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_registered')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_registered')->default(true)->after('password');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'preferences')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('preferences')->nullable()->after('guest_session_id');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'last_activity')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_activity')->nullable()->after('preferences');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'organization_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('password');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('organization_id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys and columns in reverse order
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
                if (Schema::hasColumn('users', 'organization_id')) {
                    $table->dropForeign(['organization_id']);
                    $table->dropColumn('organization_id');
                }
                if (Schema::hasColumn('users', 'last_activity')) {
                    $table->dropColumn('last_activity');
                }
                if (Schema::hasColumn('users', 'preferences')) {
                    $table->dropColumn('preferences');
                }
                if (Schema::hasColumn('users', 'is_registered')) {
                    $table->dropColumn('is_registered');
                }
                if (Schema::hasColumn('users', 'guest_session_id')) {
                    $table->dropColumn('guest_session_id');
                }
            });
        }

        if (Schema::hasTable('menu_items')) {
            Schema::table('menu_items', function (Blueprint $table) {
                if (Schema::hasColumn('menu_items', 'featured')) {
                    $table->dropColumn('featured');
                }
                if (Schema::hasColumn('menu_items', 'image_url')) {
                    $table->dropColumn('image_url');
                }
                if (Schema::hasColumn('menu_items', 'ingredients')) {
                    $table->dropColumn('ingredients');
                }
                if (Schema::hasColumn('menu_items', 'allergen_info')) {
                    $table->dropColumn('allergen_info');
                }
                if (Schema::hasColumn('menu_items', 'dietary_info')) {
                    $table->dropColumn('dietary_info');
                }
                if (Schema::hasColumn('menu_items', 'spice_level')) {
                    $table->dropColumn('spice_level');
                }
            });
        }

        if (Schema::hasTable('item_masters') && Schema::hasColumn('item_masters', 'supplier_id')) {
            Schema::table('item_masters', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            });
        }

        if (Schema::hasTable('kitchen_stations') && Schema::hasColumn('kitchen_stations', 'organization_id')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }
    }
};
