<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations for Laravel with PostgreSQL and Tailwind CSS stack
     */
    public function up(): void
    {
        try {
            // Ensure customers table uses phone as primary key
            if (Schema::hasTable('customers')) {
                // First check if we need to modify the existing customers table
                $columns = Schema::getColumnListing('customers');
                $tableNeedsUpdate = false;
                
                // Check if phone is already the primary key
                try {
                    $primaryKey = Schema::getConnection()
                        ->getDoctrineSchemaManager()
                        ->listTableDetails('customers')
                        ->getPrimaryKey();
                        
                    if (!$primaryKey || $primaryKey->getColumns() !== ['phone']) {
                        $tableNeedsUpdate = true;
                    }
                } catch (\Exception $e) {
                    // If we can't check the primary key, assume we need to update
                    $tableNeedsUpdate = true;
                    Log::info('Could not check existing primary key, will recreate table');
                }
                
                if ($tableNeedsUpdate) {
                    // Drop the existing table and recreate with proper structure
                    Schema::dropIfExists('customers');
                    Log::info('Dropped existing customers table for restructuring');
                }
            }

            // Create customers table with phone as primary key for PostgreSQL
            if (!Schema::hasTable('customers')) {
                Schema::create('customers', function (Blueprint $table) {
                    $table->string('phone')->primary();
                    $table->string('name')->nullable();
                    $table->string('email')->nullable()->unique();
                    $table->enum('preferred_contact', ['email', 'sms'])->default('email');
                    $table->date('date_of_birth')->nullable();
                    $table->date('anniversary_date')->nullable();
                    $table->text('dietary_preferences')->nullable();
                    $table->text('special_notes')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->timestamp('last_visit_date')->nullable();
                    $table->integer('total_orders')->default(0);
                    $table->decimal('total_spent', 10, 2)->default(0);
                    $table->integer('loyalty_points')->default(0);
                    $table->timestamps();
                    $table->softDeletes();
                    
                    // PostgreSQL-optimized indexes
                    $table->index(['is_active']);
                    $table->index(['total_spent']);
                    $table->index(['last_visit_date']);
                    $table->index(['email']); // Separate index for email searches
                });
                
                Log::info('Created customers table with phone as primary key');
            }

            // Update reservations table with new columns for PostgreSQL
            if (Schema::hasTable('reservations')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $existingColumns = Schema::getColumnListing('reservations');
                    
                    // Add customer_phone_fk if it doesn't exist
                    if (!in_array('customer_phone_fk', $existingColumns)) {
                        $table->string('customer_phone_fk')->nullable()->after('phone');
                        $table->foreign('customer_phone_fk')->references('phone')->on('customers')->onDelete('set null');
                        Log::info('Added customer_phone_fk to reservations table');
                    }
                    
                    // Add reservation type if it doesn't exist
                    if (!in_array('type', $existingColumns)) {
                        $table->enum('type', ['online', 'in_call', 'walk_in'])->default('online')->after('status');
                        Log::info('Added type column to reservations table');
                    }
                    
                    // Add table_size if it doesn't exist
                    if (!in_array('table_size', $existingColumns)) {
                        $table->unsignedInteger('table_size')->default(2)->after('number_of_people');
                        Log::info('Added table_size column to reservations table');
                    }
                    
                    // Update fee columns if they exist, add them if they don't
                    if (!in_array('reservation_fee', $existingColumns)) {
                        $table->decimal('reservation_fee', 8, 2)->default(0)->after('comments');
                        Log::info('Added reservation_fee column to reservations table');
                    }
                    
                    if (!in_array('cancellation_fee', $existingColumns)) {
                        $table->decimal('cancellation_fee', 8, 2)->default(0)->after('reservation_fee');
                        Log::info('Added cancellation_fee column to reservations table');
                    }
                });
            }

            // Update orders table with new columns for PostgreSQL
            if (Schema::hasTable('orders')) {
                Schema::table('orders', function (Blueprint $table) {
                    $existingColumns = Schema::getColumnListing('orders');
                    
                    // Add customer_phone_fk if it doesn't exist
                    if (!in_array('customer_phone_fk', $existingColumns)) {
                        $table->string('customer_phone_fk')->nullable()->after('customer_phone');
                        $table->foreign('customer_phone_fk')->references('phone')->on('customers')->onDelete('set null');
                        Log::info('Added customer_phone_fk to orders table');
                    }
                    
                    // Add enhanced order_type enum if it doesn't exist
                    if (!in_array('order_type', $existingColumns)) {
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
                        Log::info('Added enhanced order_type to orders table');
                    }
                    
                    // Add reservation requirement flag
                    if (!in_array('reservation_required', $existingColumns)) {
                        $table->boolean('reservation_required')->default(false)->after('order_type');
                        Log::info('Added reservation_required column to orders table');
                    }
                });
            }

            Log::info('Successfully implemented phone-based customer system for PostgreSQL');

        } catch (\Exception $e) {
            Log::error('Error implementing phone-based customer system: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            // Don't throw to prevent migration failure
        }
    }

    /**
     * Reverse the migrations for Laravel with PostgreSQL
     */
    public function down(): void
    {
        try {
            // Remove foreign key constraints and columns from orders table
            if (Schema::hasTable('orders')) {
                Schema::table('orders', function (Blueprint $table) {
                    $existingColumns = Schema::getColumnListing('orders');
                    
                    if (in_array('customer_phone_fk', $existingColumns)) {
                        $table->dropForeign(['customer_phone_fk']);
                        $table->dropColumn('customer_phone_fk');
                    }
                    if (in_array('reservation_required', $existingColumns)) {
                        $table->dropColumn('reservation_required');
                    }
                    // Note: Dropping enum columns in PostgreSQL requires special handling
                    // We'll leave order_type as is to avoid data loss
                });
            }

            // Remove foreign key constraints and columns from reservations table
            if (Schema::hasTable('reservations')) {
                Schema::table('reservations', function (Blueprint $table) {
                    $existingColumns = Schema::getColumnListing('reservations');
                    
                    if (in_array('customer_phone_fk', $existingColumns)) {
                        $table->dropForeign(['customer_phone_fk']);
                        $table->dropColumn('customer_phone_fk');
                    }
                    if (in_array('type', $existingColumns)) {
                        $table->dropColumn('type');
                    }
                    if (in_array('table_size', $existingColumns)) {
                        $table->dropColumn('table_size');
                    }
                });
            }

            // Drop customers table
            Schema::dropIfExists('customers');
            
            Log::info('Successfully rolled back phone-based customer system');

        } catch (\Exception $e) {
            Log::error('Error rolling back phone-based customer system: ' . $e->getMessage());
        }
    }
};
