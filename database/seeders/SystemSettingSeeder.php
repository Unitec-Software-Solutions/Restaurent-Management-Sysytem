<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('⚙️ Seeding system settings...');

        $this->seedSystemSettings();
    }

    /**
     * Seed system settings using multiple approaches
     */
    private function seedSystemSettings()
    {
        $settings = [
            // General System Settings
            ['key' => 'app_name', 'value' => 'Restaurant Management System', 'type' => 'string', 'category' => 'general'],
            ['key' => 'default_currency', 'value' => 'USD', 'type' => 'string', 'category' => 'general'],
            ['key' => 'currency_symbol', 'value' => '$', 'type' => 'string', 'category' => 'general'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'category' => 'general'],
            
            // Restaurant Operations
            ['key' => 'default_service_charge', 'value' => '10.00', 'type' => 'decimal', 'category' => 'restaurant'],
            ['key' => 'default_tax_rate', 'value' => '7.50', 'type' => 'decimal', 'category' => 'restaurant'],
            ['key' => 'enable_table_reservations', 'value' => 'true', 'type' => 'boolean', 'category' => 'restaurant'],
            ['key' => 'default_preparation_time', 'value' => '15', 'type' => 'integer', 'category' => 'restaurant'],
            ['key' => 'enable_takeaway', 'value' => 'true', 'type' => 'boolean', 'category' => 'restaurant'],
            ['key' => 'enable_delivery', 'value' => 'true', 'type' => 'boolean', 'category' => 'restaurant'],
            
            // Inventory Management
            ['key' => 'enable_auto_stock_deduction', 'value' => 'true', 'type' => 'boolean', 'category' => 'inventory'],
            ['key' => 'low_stock_alert_threshold', 'value' => '5', 'type' => 'integer', 'category' => 'inventory'],
            ['key' => 'enable_expiry_alerts', 'value' => 'true', 'type' => 'boolean', 'category' => 'inventory'],
            
            // Payment Settings
            ['key' => 'enable_cash_payments', 'value' => 'true', 'type' => 'boolean', 'category' => 'payment'],
            ['key' => 'enable_card_payments', 'value' => 'true', 'type' => 'boolean', 'category' => 'payment'],
            ['key' => 'enable_digital_wallet', 'value' => 'true', 'type' => 'boolean', 'category' => 'payment'],
            ['key' => 'payment_timeout_minutes', 'value' => '15', 'type' => 'integer', 'category' => 'payment'],
            
            // Kitchen Management
            ['key' => 'enable_kot_printing', 'value' => 'true', 'type' => 'boolean', 'category' => 'kitchen'],
            ['key' => 'auto_print_kot', 'value' => 'true', 'type' => 'boolean', 'category' => 'kitchen'],
            ['key' => 'enable_order_priority', 'value' => 'true', 'type' => 'boolean', 'category' => 'kitchen'],
            
            // User Management
            ['key' => 'require_email_verification', 'value' => 'true', 'type' => 'boolean', 'category' => 'user'],
            ['key' => 'password_min_length', 'value' => '8', 'type' => 'integer', 'category' => 'user'],
            ['key' => 'session_timeout_minutes', 'value' => '480', 'type' => 'integer', 'category' => 'user'],
            
            // UI/UX Settings
            ['key' => 'default_theme', 'value' => 'light', 'type' => 'string', 'category' => 'ui'],
            ['key' => 'items_per_page', 'value' => '25', 'type' => 'integer', 'category' => 'ui'],
            ['key' => 'default_language', 'value' => 'en', 'type' => 'string', 'category' => 'ui'],
        ];

        // Try different table names that might exist
        $possibleTables = ['system_settings', 'settings', 'app_settings', 'configurations', 'config'];

        $tableFound = false;
        foreach ($possibleTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                $this->seedToTable($tableName, $settings);
                $tableFound = true;
                break;
            }
        }

        if (!$tableFound) {
            $this->command->warn('No system settings table found. Creating basic config file...');
            $this->createBasicConfigFile($settings);
        }

        $this->command->info('✅ System settings seeded successfully');
    }

    /**
     * Seed to a specific table
     */
    private function seedToTable($tableName, $settings)
    {
        $this->command->info("Seeding to {$tableName} table...");
        
        foreach ($settings as $setting) {
            try {
                $columns = Schema::getColumnListing($tableName);
                
                $data = [
                    'key' => $setting['key'],
                    'value' => $setting['value'],
                ];

                if (in_array('type', $columns)) {
                    $data['type'] = $setting['type'];
                }
                if (in_array('category', $columns)) {
                    $data['category'] = $setting['category'];
                }
                if (in_array('created_at', $columns)) {
                    $data['created_at'] = now();
                    $data['updated_at'] = now();
                }

                DB::table($tableName)->updateOrInsert(
                    ['key' => $setting['key']],
                    $data
                );
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    /**
     * Create basic config file as fallback
     */
    private function createBasicConfigFile($settings)
    {
        $basicSettings = array_slice($settings, 0, 10);
        
        $configContent = "<?php\n\n// Auto-generated system settings\nreturn [\n";
        foreach ($basicSettings as $setting) {
            $configContent .= "    '{$setting['key']}' => '{$setting['value']}',\n";
        }
        $configContent .= "];\n";

        try {
            file_put_contents(config_path('auto_system_settings.php'), $configContent);
            $this->command->info('Created basic config file: config/auto_system_settings.php');
        } catch (\Exception $e) {
            $this->command->warn('Could not create config file. Settings will need to be configured manually.');
        }
    }
}
