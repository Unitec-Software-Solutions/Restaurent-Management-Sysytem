<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Update order_type to use the new enum values
            if (Schema::hasColumn('orders', 'order_type')) {
                // First, set a default value for existing records
                DB::statement("UPDATE orders SET order_type = 'dine_in_walk_in_demand' WHERE order_type IS NULL OR order_type = ''");
                
                // Then modify the column
                $table->enum('order_type', OrderType::values())
                      ->default(OrderType::DINE_IN_WALK_IN_DEMAND->value)
                      ->change();
            } else {
                $table->enum('order_type', OrderType::values())
                      ->default(OrderType::DINE_IN_WALK_IN_DEMAND->value)
                      ->after('id');
            }

            // Add customer phone relationship
            if (!Schema::hasColumn('orders', 'customer_phone_fk')) {
                $table->string('customer_phone_fk')->nullable()->after('customer_phone');
                $table->foreign('customer_phone_fk')->references('phone')->on('customers')->onDelete('set null');
            }

            // Add reservation requirement validation flag
            if (!Schema::hasColumn('orders', 'reservation_required')) {
                $table->boolean('reservation_required')->default(false)->after('reservation_id');
            }

            // Add order source tracking
            if (!Schema::hasColumn('orders', 'order_source')) {
                $table->enum('order_source', ['admin', 'customer', 'guest', 'api', 'pos'])
                      ->default('admin')
                      ->after('order_type');
            }

            // Add estimated preparation time
            if (!Schema::hasColumn('orders', 'estimated_prep_time')) {
                $table->unsignedInteger('estimated_prep_time')->nullable()->comment('Estimated preparation time in minutes');
            }

            // Add pickup/delivery time for takeaway orders
            if (!Schema::hasColumn('orders', 'pickup_time')) {
                $table->timestamp('pickup_time')->nullable();
            }

            // Add order priority
            if (!Schema::hasColumn('orders', 'priority')) {
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_phone_fk']);
            $table->dropColumn([
                'customer_phone_fk',
                'reservation_required',
                'order_source',
                'estimated_prep_time',
                'pickup_time',
                'priority'
            ]);
            
            // Revert order_type to simple enum
            $table->enum('order_type', ['takeaway', 'dine_in', 'delivery'])
                  ->default('takeaway')
                  ->change();
        });
    }
};
