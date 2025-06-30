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
        // Ensure customers table uses phone as primary key
        if (Schema::hasTable('customers')) {
            // First check if we need to modify the existing customers table
            $columns = Schema::getColumnListing('customers');
            $tableNeedsUpdate = false;
            
            // Check if phone is already the primary key
            $primaryKey = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableDetails('customers')
                ->getPrimaryKey();
                
            if (!$primaryKey || $primaryKey->getColumns() !== ['phone']) {
                $tableNeedsUpdate = true;
            }
            
            if ($tableNeedsUpdate) {
                // Drop the existing table and recreate with proper structure
                Schema::dropIfExists('customers');
            }
        }

        // Create customers table with phone as primary key
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->string('phone')->primary();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->enum('preferred_contact', ['email', 'sms'])->default('email');
                $table->date('date_of_birth')->nullable();
                $table->date('anniversary_date')->nullable();
                $table->text('dietary_preferences')->nullable();
                $table->text('special_notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->datetime('last_visit_date')->nullable();
                $table->integer('total_orders')->default(0);
                $table->decimal('total_spent', 10, 2)->default(0);
                $table->integer('loyalty_points')->default(0);
                $table->timestamps();
                $table->softDeletes();
                
                // Indexes for performance
                $table->index(['is_active']);
                $table->index(['total_spent']);
                $table->index(['last_visit_date']);
            });
        }

        // Update reservations table with new columns
        Schema::table('reservations', function (Blueprint $table) {
            // Add reservation type if it doesn't exist
            if (!Schema::hasColumn('reservations', 'type')) {
                $table->enum('type', ['online', 'in_call', 'walk_in'])->default('online')->after('status');
            }
            
            // Add table_size if it doesn't exist
            if (!Schema::hasColumn('reservations', 'table_size')) {
                $table->unsignedInteger('table_size')->default(2)->after('number_of_people');
            }
            
            // Update fee columns if they exist, add them if they don't
            if (!Schema::hasColumn('reservations', 'reservation_fee')) {
                $table->decimal('reservation_fee', 8, 2)->default(0)->after('comments');
            }
            
            if (!Schema::hasColumn('reservations', 'cancellation_fee')) {
                $table->decimal('cancellation_fee', 8, 2)->default(0)->after('reservation_fee');
            }
            
            // Add foreign key to customers if it doesn't exist
            if (!Schema::hasColumn('reservations', 'customer_phone_fk')) {
                $table->string('customer_phone_fk')->nullable()->after('phone');
                $table->foreign('customer_phone_fk')->references('phone')->on('customers')->onDelete('set null');
            }
        });

        // Update orders table with new columns and relationships
        Schema::table('orders', function (Blueprint $table) {
            // Add customer phone foreign key if it doesn't exist
            if (!Schema::hasColumn('orders', 'customer_phone_fk')) {
                $table->string('customer_phone_fk')->nullable()->after('customer_phone');
                $table->foreign('customer_phone_fk')->references('phone')->on('customers')->onDelete('set null');
            }
            
            // Update order_type column to use the new enum values
            if (Schema::hasColumn('orders', 'order_type')) {
                // Note: We'll handle the enum change in the model and validation
                // since changing enum columns can be tricky with existing data
            } else {
                $table->enum('order_type', [
                    'takeaway_in_call_scheduled',
                    'takeaway_online_scheduled', 
                    'takeaway_walk_in_scheduled',
                    'takeaway_walk_in_demand',
                    'dine_in_online_scheduled',
                    'dine_in_in_call_scheduled',
                    'dine_in_walk_in_scheduled',
                    'dine_in_walk_in_demand'
                ])->default('dine_in_walk_in_demand')->after('customer_email');
            }
            
            // Add reservation requirement flag
            if (!Schema::hasColumn('orders', 'reservation_required')) {
                $table->boolean('reservation_required')->default(false)->after('order_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'customer_phone_fk')) {
                $table->dropForeign(['customer_phone_fk']);
                $table->dropColumn('customer_phone_fk');
            }
            if (Schema::hasColumn('orders', 'reservation_required')) {
                $table->dropColumn('reservation_required');
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'customer_phone_fk')) {
                $table->dropForeign(['customer_phone_fk']);
                $table->dropColumn('customer_phone_fk');
            }
            if (Schema::hasColumn('reservations', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('reservations', 'table_size')) {
                $table->dropColumn('table_size');
            }
        });

        Schema::dropIfExists('customers');
    }
};
