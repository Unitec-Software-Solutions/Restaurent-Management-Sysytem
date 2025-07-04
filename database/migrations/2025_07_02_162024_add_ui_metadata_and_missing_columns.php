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
        // Add ui_metadata to kitchen_stations table
        if (Schema::hasTable('kitchen_stations') && !Schema::hasColumn('kitchen_stations', 'ui_metadata')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                $table->json('ui_metadata')->nullable()->after('equipment_list');
            });
        }

        // Add sku and related columns to item_master table
        if (Schema::hasTable('item_master')) {
            if (!Schema::hasColumn('item_master', 'sku')) {
                Schema::table('item_master', function (Blueprint $table) {
                    $table->string('sku')->nullable()->after('item_code');
                });
            }
            if (!Schema::hasColumn('item_master', 'unit')) {
                Schema::table('item_master', function (Blueprint $table) {
                    $table->string('unit')->default('pcs')->after('sku');
                });
            }
            if (!Schema::hasColumn('item_master', 'unit_of_measurement')) {
                Schema::table('item_master', function (Blueprint $table) {
                    $table->string('unit_of_measurement')->default('pcs')->after('unit');
                });
            }
            if (!Schema::hasColumn('item_master', 'purchase_price')) {
                Schema::table('item_master', function (Blueprint $table) {
                    $table->decimal('purchase_price', 10, 2)->default(0)->after('unit_of_measurement');
                });
            }
            if (!Schema::hasColumn('item_master', 'minimum_stock')) {
                Schema::table('item_master', function (Blueprint $table) {
                    $table->integer('minimum_stock')->default(0)->after('purchase_price');
                });
            }
            if (!Schema::hasColumn('item_master', 'maximum_stock')) {
                Schema::table('item_master', function (Blueprint $table) {
                    $table->integer('maximum_stock')->default(100)->after('minimum_stock');
                });
            }
            if (!Schema::hasColumn('item_master', 'is_inventory_item')) {
                Schema::table('item_master', function (Blueprint $table) {
                    $table->boolean('is_inventory_item')->default(true)->after('maximum_stock');
                });
            }
            if (!Schema::hasColumn('item_master', 'storage_requirements')) {
                Schema::table('item_master', function (Blueprint $table) {
                    $table->text('storage_requirements')->nullable()->after('is_inventory_item');
                });
            }
            if (!Schema::hasColumn('item_master', 'shelf_life_days')) {
                Schema::table('item_master', function (Blueprint $table) {
                    $table->integer('shelf_life_days')->nullable()->after('storage_requirements');
                });
            }
        }

        // Add key and is_active columns to restaurant_configs table
        if (Schema::hasTable('restaurant_configs')) {
            if (!Schema::hasColumn('restaurant_configs', 'key')) {
                Schema::table('restaurant_configs', function (Blueprint $table) {
                    $table->string('key')->after('branch_id');
                });
            }
            if (!Schema::hasColumn('restaurant_configs', 'value')) {
                Schema::table('restaurant_configs', function (Blueprint $table) {
                    $table->text('value')->nullable()->after('key');
                });
            }
            if (!Schema::hasColumn('restaurant_configs', 'is_active')) {
                Schema::table('restaurant_configs', function (Blueprint $table) {
                    $table->boolean('is_active')->default(true)->after('value');
                });
            }
        }

        // Add organization_id to reservations table
        if (Schema::hasTable('reservations') && !Schema::hasColumn('reservations', 'organization_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            });
        }

        // Add table_id to reservations table
        if (Schema::hasTable('reservations') && !Schema::hasColumn('reservations', 'table_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->unsignedBigInteger('table_id')->nullable()->after('branch_id');
                $table->foreign('table_id')->references('id')->on('tables')->onDelete('set null');
            });
        }

        // Add hired_at to admins table
        if (Schema::hasTable('admins') && !Schema::hasColumn('admins', 'hired_at')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->timestamp('hired_at')->nullable()->after('status');
            });
        }

        // Add guest_order to orders table
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'guest_order')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('guest_order')->default(false)->after('delivery_address');
            });
        }

        // Add device_info to orders table
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'device_info')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->json('device_info')->nullable()->after('guest_order');
            });
        }

        // Add customer_name, customer_phone, customer_email to reservations table
        if (Schema::hasTable('reservations')) {
            if (!Schema::hasColumn('reservations', 'customer_name')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->string('customer_name')->nullable()->after('steward_id');
                });
            }
            if (!Schema::hasColumn('reservations', 'customer_phone')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->string('customer_phone')->nullable()->after('customer_name');
                });
            }
            if (!Schema::hasColumn('reservations', 'customer_email')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $table->string('customer_email')->nullable()->after('customer_phone');
                });
            }
        }

        // Add device_info to orders table
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'device_info')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->json('device_info')->nullable()->after('guest_order');
            });
        }

        // Add customer_name to reservations table
        if (Schema::hasTable('reservations') && !Schema::hasColumn('reservations', 'customer_name')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->string('customer_name')->nullable()->after('steward_id');
            });
        }

        // Add customer_phone to reservations table
        if (Schema::hasTable('reservations') && !Schema::hasColumn('reservations', 'customer_phone')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->string('customer_phone')->nullable()->after('customer_name');
            });
        }

        // Add customer_email to reservations table
        if (Schema::hasTable('reservations') && !Schema::hasColumn('reservations', 'customer_email')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->string('customer_email')->nullable()->after('customer_phone');
            });
        }

        // Add party_size to reservations table
        if (Schema::hasTable('reservations') && !Schema::hasColumn('reservations', 'party_size')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->integer('party_size')->default(1)->after('customer_email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove ui_metadata from kitchen_stations
        if (Schema::hasColumn('kitchen_stations', 'ui_metadata')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                $table->dropColumn('ui_metadata');
            });
        }

        // Remove added columns from item_master
        Schema::table('item_master', function (Blueprint $table) {
            $columns = ['sku', 'unit', 'unit_of_measurement', 'purchase_price', 'minimum_stock', 'maximum_stock', 'is_inventory_item', 'storage_requirements', 'shelf_life_days'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('item_master', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Remove added columns from restaurant_configs
        Schema::table('restaurant_configs', function (Blueprint $table) {
            $columns = ['key', 'value', 'is_active'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('restaurant_configs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Remove organization_id from reservations
        if (Schema::hasColumn('reservations', 'organization_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }

        // Remove table_id from reservations
        if (Schema::hasColumn('reservations', 'table_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropForeign(['table_id']);
                $table->dropColumn('table_id');
            });
        }

        // Remove hired_at from admins
        if (Schema::hasColumn('admins', 'hired_at')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('hired_at');
            });
        }

        // Remove guest_order from orders
        if (Schema::hasColumn('orders', 'guest_order')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('guest_order');
            });
        }

        // Remove device_info from orders
        if (Schema::hasColumn('orders', 'device_info')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('device_info');
            });
        }

        // Remove customer fields from reservations
        Schema::table('reservations', function (Blueprint $table) {
            $columns = ['customer_name', 'customer_phone', 'customer_email'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('reservations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Remove device_info from orders
        if (Schema::hasColumn('orders', 'device_info')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('device_info');
            });
        }

        // Remove customer fields from reservations
        Schema::table('reservations', function (Blueprint $table) {
            $columns = ['customer_name', 'customer_phone', 'customer_email', 'party_size'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('reservations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
