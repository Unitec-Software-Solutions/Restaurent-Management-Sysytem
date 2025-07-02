<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations for Laravel + PostgreSQL + Tailwind CSS stack
     */
    public function up(): void
    {
        if (Schema::hasTable('kitchen_stations')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('kitchen_stations');
                Log::info('Current kitchen_stations columns:', $existingColumns);
                
                // Add max_concurrent_orders column if it doesn't exist
                if (!in_array('max_concurrent_orders', $existingColumns)) {
                    $table->integer('max_concurrent_orders')->default(5)->after('order_priority');
                    Log::info('Added max_concurrent_orders column to kitchen_stations table');
                }
                
                // Add code column if it doesn't exist (required by models)
                if (!in_array('code', $existingColumns)) {
                    $table->string('code')->unique()->after('name');
                    Log::info('Added code column to kitchen_stations table');
                }
                
                // Add max_capacity column if it doesn't exist
                if (!in_array('max_capacity', $existingColumns)) {
                    $table->decimal('max_capacity', 8, 2)->nullable()->after('max_concurrent_orders');
                    Log::info('Added max_capacity column to kitchen_stations table');
                }
                
                // Add description column if it doesn't exist
                if (!in_array('description', $existingColumns)) {
                    $table->text('description')->nullable()->after('type');
                    Log::info('Added description column to kitchen_stations table');
                }
                
                // Add printer_config column if it doesn't exist (PostgreSQL JSON)
                if (!in_array('printer_config', $existingColumns)) {
                    $table->json('printer_config')->nullable()->after('max_capacity');
                    Log::info('Added printer_config column to kitchen_stations table');
                }
                
                // Add settings column if it doesn't exist (PostgreSQL JSON)
                if (!in_array('settings', $existingColumns)) {
                    $table->json('settings')->nullable()->after('printer_config');
                    Log::info('Added settings column to kitchen_stations table');
                }
                
                // Add notes column if it doesn't exist or recreate if needed
                if (!in_array('notes', $existingColumns)) {
                    $table->text('notes')->nullable()->after('settings');
                    Log::info('Added notes column to kitchen_stations table');
                }
                
                // Add soft deletes column if it doesn't exist
                if (!in_array('deleted_at', $existingColumns)) {
                    $table->softDeletes();
                    Log::info('Added deleted_at column to kitchen_stations table');
                }
                
                // Add additional useful columns for restaurant operations
                if (!in_array('equipment_list', $existingColumns)) {
                    $table->json('equipment_list')->nullable()->after('settings');
                    Log::info('Added equipment_list column to kitchen_stations table');
                }
                
                if (!in_array('current_orders', $existingColumns)) {
                    $table->integer('current_orders')->default(0)->after('max_concurrent_orders');
                    Log::info('Added current_orders column to kitchen_stations table');
                }
                
                if (!in_array('station_type', $existingColumns)) {
                    $table->string('station_type')->default('standard')->after('type');
                    Log::info('Added station_type column to kitchen_stations table');
                }
                
                if (!in_array('priority_level', $existingColumns)) {
                    $table->integer('priority_level')->default(1)->after('order_priority');
                    Log::info('Added priority_level column to kitchen_stations table');
                }
                
                // Add indexes for PostgreSQL performance optimization
                try {
                    $table->index(['branch_id', 'is_active'], );
                    $table->index(['type']);
                    $table->index(['station_type'] );
                    $table->index(['order_priority'], 'kitchen_stations_priority_idx');
                    Log::info('Added performance indexes to kitchen_stations table');
                } catch (\Exception $e) {
                    Log::warning('Some indexes already exist or could not be created: ' . $e->getMessage());
                }
            });
            
            // Update existing kitchen stations with default values
            $this->updateExistingKitchenStations();
            
        } else {
            Log::warning('kitchen_stations table does not exist');
        }
    }
    
    /**
     * Update existing kitchen stations with default values for PostgreSQL
     */
    private function updateExistingKitchenStations(): void
    {
        try {
            // Set default values for existing records
            DB::table('kitchen_stations')->update([
                'max_concurrent_orders' => DB::raw('COALESCE(max_concurrent_orders, 5)'),
                'current_orders' => DB::raw('COALESCE(current_orders, 0)'),
                'priority_level' => DB::raw('COALESCE(priority_level, 1)'),
                'station_type' => DB::raw("COALESCE(station_type, 'standard')"),
                'max_capacity' => DB::raw('COALESCE(max_capacity, 50.00)')
            ]);
            
            // Set default printer config for PostgreSQL JSON
            $defaultPrinterConfig = json_encode([
                'printer_ip' => '192.168.1.100',
                'printer_name' => 'Kitchen Printer',
                'paper_size' => '80mm',
                'auto_print' => false,
                'print_logo' => true,
                'print_quality' => 'standard'
            ]);
            
            DB::table('kitchen_stations')
                ->whereNull('printer_config')
                ->update(['printer_config' => $defaultPrinterConfig]);
            
            // Set default settings for PostgreSQL JSON
            $defaultSettings = json_encode([
                'temperature_monitoring' => false,
                'order_timeout_minutes' => 30,
                'auto_notify_delays' => true,
                'preparation_notes_required' => false,
                'quality_check_required' => false,
                'sound_alerts' => true,
                'visual_alerts' => true
            ]);
            
            DB::table('kitchen_stations')
                ->whereNull('settings')
                ->update(['settings' => $defaultSettings]);
            
            // Set default equipment list for PostgreSQL JSON
            $defaultEquipment = json_encode([
                'stove' => true,
                'oven' => true,
                'grill' => false,
                'fryer' => false,
                'mixer' => false,
                'refrigerator' => true
            ]);
            
            DB::table('kitchen_stations')
                ->whereNull('equipment_list')
                ->update(['equipment_list' => $defaultEquipment]);
            
            // Generate codes for stations that don't have them
            $stationsWithoutCodes = DB::table('kitchen_stations')
                ->whereNull('code')
                ->orWhere('code', '')
                ->get();
            
            foreach ($stationsWithoutCodes as $station) {
                $typePrefix = match($station->type) {
                    'cooking' => 'COOK',
                    'prep' => 'PREP',
                    'beverage' => 'BEV',
                    'dessert' => 'DESS',
                    'grill' => 'GRILL',
                    'fry' => 'FRY',
                    'bar' => 'BAR',
                    default => 'MAIN'
                };
                
                $branchCode = str_pad($station->branch_id, 3, '0', STR_PAD_LEFT);
                $sequenceCode = str_pad($station->id, 2, '0', STR_PAD_LEFT);
                $newCode = $typePrefix . '_' . $branchCode . '_' . $sequenceCode;
                
                // Ensure uniqueness
                $counter = 1;
                $originalCode = $newCode;
                while (DB::table('kitchen_stations')->where('code', $newCode)->where('id', '!=', $station->id)->exists()) {
                    $newCode = $originalCode . '_' . $counter;
                    $counter++;
                }
                
                DB::table('kitchen_stations')
                    ->where('id', $station->id)
                    ->update(['code' => $newCode]);
                    
                Log::info("Generated code '{$newCode}' for kitchen station ID {$station->id}");
            }
            
            Log::info('Updated existing kitchen stations with default values and codes');
            
        } catch (\Exception $e) {
            Log::warning('Could not update existing kitchen stations: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations for PostgreSQL
     */
    public function down(): void
    {
        if (Schema::hasTable('kitchen_stations')) {
            Schema::table('kitchen_stations', function (Blueprint $table) {
                $existingColumns = Schema::getColumnListing('kitchen_stations');
                
                $columnsToRemove = [
                    'max_concurrent_orders', 'code', 'max_capacity', 'description',
                    'printer_config', 'settings', 'equipment_list', 'current_orders',
                    'station_type', 'priority_level', 'deleted_at'
                ];
                
                foreach ($columnsToRemove as $column) {
                    if (in_array($column, $existingColumns)) {
                        if ($column === 'deleted_at') {
                            $table->dropSoftDeletes();
                        } else {
                            $table->dropColumn($column);
                        }
                    }
                }
                
                // Drop indexes
                try {
                    $table->dropIndex('kitchen_stations_type_idx');
                    $table->dropIndex('kitchen_stations_station_type_idx');
                    $table->dropIndex('kitchen_stations_priority_idx');
                } catch (\Exception $e) {
                    Log::warning('Some indexes could not be dropped: ' . $e->getMessage());
                }
            });
        }
    }
};