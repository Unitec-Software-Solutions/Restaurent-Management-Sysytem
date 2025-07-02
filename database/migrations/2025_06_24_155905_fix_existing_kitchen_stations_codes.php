<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations for Laravel + PostgreSQL + Tailwind CSS stack
     */
    public function up(): void
    {
        try {
            
            $stationsWithoutCodes = DB::table('kitchen_stations')
                ->whereNull('code')
                ->orWhere('code', '')
                ->get();

            Log::info('Found ' . $stationsWithoutCodes->count() . ' kitchen stations without codes');

            foreach ($stationsWithoutCodes as $station) {
                $typePrefix = match($station->type) {
                    'cooking' => 'COOK',
                    'prep' => 'PREP',
                    'preparation' => 'PREP',
                    'beverage' => 'BEV',
                    'dessert' => 'DESS',
                    'grilling' => 'GRILL',
                    'grill' => 'GRILL',
                    'fry' => 'FRY',
                    'bar' => 'BAR',
                    default => 'MAIN'
                };
                
                $branchCode = str_pad($station->branch_id, 2, '0', STR_PAD_LEFT);
                $sequenceCode = str_pad($station->id, 3, '0', STR_PAD_LEFT);
                $newCode = $typePrefix . '-' . $branchCode . '-' . $sequenceCode;
                
                // Ensure code uniqueness
                $counter = 1;
                $originalCode = $newCode;
                while (DB::table('kitchen_stations')->where('code', $newCode)->where('id', '!=', $station->id)->exists()) {
                    $newCode = $originalCode . '-' . $counter;
                    $counter++;
                }
                
                DB::table('kitchen_stations')
                    ->where('id', $station->id)
                    ->update([
                        'code' => $newCode,
                        'updated_at' => now()
                    ]);

                Log::info("Generated code '{$newCode}' for kitchen station ID {$station->id}");
            }

            // Ensure all kitchen stations have required fields for PostgreSQL
            $this->ensureRequiredFields();
            
            Log::info('Successfully fixed kitchen station codes');

        } catch (\Exception $e) {
            Log::error('Error fixing kitchen station codes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ensure all kitchen stations have required fields for PostgreSQL
     */
    private function ensureRequiredFields(): void
    {
        try {
            $existingColumns = Schema::getColumnListing('kitchen_stations');
            
            // Update stations with missing required fields
            $updates = [];
            
            if (in_array('max_concurrent_orders', $existingColumns)) {
                $updates['max_concurrent_orders'] = DB::raw('COALESCE(max_concurrent_orders, 5)');
            }
            
            if (in_array('order_priority', $existingColumns)) {
                $updates['order_priority'] = DB::raw('COALESCE(order_priority, 1)');
            }
            
            if (in_array('is_active', $existingColumns)) {
                $updates['is_active'] = DB::raw('COALESCE(is_active, true)');
            }

            if (!empty($updates)) {
                DB::table('kitchen_stations')
                    ->whereNull('max_concurrent_orders')
                    ->orWhereNull('order_priority')
                    ->orWhereNull('is_active')
                    ->update($updates);
                
                Log::info('Updated kitchen stations with default values');
            }

            // Set default printer config for stations without one (PostgreSQL JSON)
            if (in_array('printer_config', $existingColumns)) {
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
                    
                Log::info('Set default printer config for kitchen stations');
            }

            // Set default settings for stations (PostgreSQL JSON)
            if (in_array('settings', $existingColumns)) {
                $defaultSettings = json_encode([
                    'temperature_monitoring' => false,
                    'order_timeout_minutes' => 30,
                    'auto_notify_delays' => true,
                    'preparation_notes_required' => false,
                    'quality_check_required' => false
                ]);

                DB::table('kitchen_stations')
                    ->whereNull('settings')
                    ->update(['settings' => $defaultSettings]);
                    
                Log::info('Set default settings for kitchen stations');
            }

        } catch (\Exception $e) {
            Log::warning('Could not update kitchen station required fields: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations for PostgreSQL
     */
    public function down(): void
    {
        // Could revert codes if needed, but typically not necessary
        Log::info('Kitchen station code migration rollback - no action needed');
    }
};
