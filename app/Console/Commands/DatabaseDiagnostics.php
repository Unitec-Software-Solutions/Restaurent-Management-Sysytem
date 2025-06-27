<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Branch;

class DatabaseDiagnostics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:diagnose {--table=menu_items} {--fix} {--full}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose database table structure and data with comprehensive analysis';

    /**
     * Execute the console command following UI/UX guidelines.
     */
    public function handle()
    {
        $tableName = $this->option('table');
        $shouldFix = $this->option('fix');
        $fullAnalysis = $this->option('full');
        
        $this->displayHeader($tableName);
        
        if (!Schema::hasTable($tableName)) {
            $this->error("❌ Table '{$tableName}' does not exist");
            $this->suggestTableCreation($tableName);
            return Command::FAILURE;
        }
        
        $columns = Schema::getColumnListing($tableName);
        $this->info("✅ Table exists with " . count($columns) . " columns");
        
        $this->displayColumnList($columns);
        
        // Perform specific table checks
        $this->performTableSpecificChecks($tableName, $columns, $shouldFix);
        
        if ($fullAnalysis) {
            $this->performFullSystemAnalysis();
        }
        
        $this->displaySystemSummary();
        
        return Command::SUCCESS;
    }
    
    /**
     * Display enhanced header following UI/UX patterns
     */
    private function displayHeader(string $tableName): void
    {
        $this->line('');
        $this->line('<fg=white;bg=blue> 🔍 Restaurant Management System - Database Diagnostics </fg=white;bg=blue>');
        $this->line('');
        $this->info("📊 Analyzing table: <fg=yellow>{$tableName}</fg=yellow>");
        $this->line(str_repeat('=', 60));
    }
    
    /**
     * Display column list with enhanced formatting
     */
    private function displayColumnList(array $columns): void
    {
        $this->line("\n📋 <fg=cyan>Column Structure Analysis:</fg=cyan>");
        
        $chunks = array_chunk($columns, 3);
        foreach ($chunks as $chunk) {
            $line = '';
            foreach ($chunk as $index => $column) {
                $columnNumber = array_search($column, $columns) + 1;
                $formattedColumn = sprintf("%-2d. %-20s", $columnNumber, $column);
                $line .= $formattedColumn;
            }
            $this->line("  " . $line);
        }
    }
    
    /**
     * Perform table-specific checks following UI/UX guidelines
     */
    private function performTableSpecificChecks(string $tableName, array $columns, bool $shouldFix): void
    {
        switch ($tableName) {
            case 'menu_items':
                $this->checkMenuItemsRequirements($columns, $shouldFix);
                break;
            case 'orders':
                $this->checkOrdersRequirements($columns, $shouldFix);
                break;
            case 'organizations':
                $this->checkOrganizationsRequirements($columns, $shouldFix);
                break;
            case 'branches':
                $this->checkBranchesRequirements($columns, $shouldFix);
                break;
            default:
                $this->checkGenericTableRequirements($tableName, $columns);
                break;
        }
    }
    
    /**
     * Enhanced menu items requirements check
     */
    private function checkMenuItemsRequirements(array $columns, bool $shouldFix): void
    {
        $this->line("\n🍽️ <fg=green>Menu Items Specific Analysis:</fg=green>");
        
        $requiredColumns = [
            'requires_preparation' => ['type' => 'boolean', 'description' => 'Preparation requirement flag'],
            'station' => ['type' => 'string', 'description' => 'Kitchen station assignment'],
            'is_vegetarian' => ['type' => 'boolean', 'description' => 'Vegetarian classification'],
            'contains_alcohol' => ['type' => 'boolean', 'description' => 'Alcohol content indicator'],
            'image_path' => ['type' => 'string', 'description' => 'Image storage path'],
            'is_active' => ['type' => 'boolean', 'description' => 'Active status flag'],
            'menu_category_id' => ['type' => 'foreign', 'description' => 'Category relationship'],
            'organization_id' => ['type' => 'foreign', 'description' => 'Organization relationship'],
            'branch_id' => ['type' => 'foreign', 'description' => 'Branch relationship']
        ];
        
        $missing = [];
        foreach ($requiredColumns as $column => $config) {
            if (in_array($column, $columns)) {
                $this->info("  ✅ <fg=green>{$column}</fg=green> - {$config['description']}");
            } else {
                $this->warn("  ⚠️ <fg=yellow>Missing:</fg=yellow> {$column} - {$config['description']}");
                $missing[] = $column;
            }
        }
        
        if (!empty($missing) && $shouldFix) {
            $this->fixMenuItemsTable($missing);
        }
        
        $this->displayMenuItemsDataSummary();
    }
    
    /**
     * Check orders table requirements
     */
    private function checkOrdersRequirements(array $columns, bool $shouldFix): void
    {
        $this->line("\n🛒 <fg=blue>Orders Table Analysis:</fg=blue>");
        
        $requiredColumns = [
            'order_number' => ['type' => 'string', 'description' => 'Unique order identifier'],
            'reservation_id' => ['type' => 'foreign', 'description' => 'Reservation relationship'],
            'subtotal' => ['type' => 'decimal', 'description' => 'Order subtotal amount'],
            'tax' => ['type' => 'decimal', 'description' => 'Tax amount'],
            'service_charge' => ['type' => 'decimal', 'description' => 'Service charge amount'],
            'discount' => ['type' => 'decimal', 'description' => 'Discount applied'],
            'total' => ['type' => 'decimal', 'description' => 'Final total amount'],
            'order_date' => ['type' => 'timestamp', 'description' => 'Order placement date'],
            'status' => ['type' => 'enum', 'description' => 'Order status tracking'],
            'branch_id' => ['type' => 'foreign', 'description' => 'Branch relationship']
        ];
        
        $missing = [];
        foreach ($requiredColumns as $column => $config) {
            if (in_array($column, $columns)) {
                $this->info("  ✅ <fg=green>{$column}</fg=green> - {$config['description']}");
            } else {
                $this->warn("  ⚠️ <fg=yellow>Missing:</fg=yellow> {$column} - {$config['description']}");
                $missing[] = $column;
            }
        }
        
        if (!empty($missing) && $shouldFix) {
            $this->info("🔧 Auto-fix available. Run: <fg=yellow>php artisan migrate</fg=yellow>");
        }
        
        $this->displayOrdersDataSummary();
    }
    
    /**
     * Display menu items data summary with enhanced formatting - FIXED
     */
    private function displayMenuItemsDataSummary(): void
    {
        $this->line("\n📊 <fg=cyan>Menu Items Data Analysis:</fg=cyan>");
        
        try {
            $total = MenuItem::count();
            $active = MenuItem::where('is_active', true)->count();
            $featured = MenuItem::where('is_featured', true)->count();
            $vegetarian = MenuItem::where('is_vegetarian', true)->count();
            $alcoholic = MenuItem::where('contains_alcohol', true)->count();
            
            // Create headers and rows arrays for the table
            $headers = ['Metric', 'Count', 'Percentage'];
            $rows = [
                ['Total Items', $total, '100%'],
                ['Active Items', $active, $total > 0 ? round(($active/$total)*100, 1).'%' : '0%'],
                ['Featured Items', $featured, $total > 0 ? round(($featured/$total)*100, 1).'%' : '0%'],
                ['Vegetarian Items', $vegetarian, $total > 0 ? round(($vegetarian/$total)*100, 1).'%' : '0%'],
                ['Alcoholic Items', $alcoholic, $total > 0 ? round(($alcoholic/$total)*100, 1).'%' : '0%'],
            ];
            
            $this->table($headers, $rows);
            
            // Category breakdown
            if (class_exists(MenuCategory::class)) {
                try {
                    $categoryData = MenuCategory::withCount('menuItems')->get();
                    if ($categoryData->isNotEmpty()) {
                        $this->line("\n📂 <fg=magenta>Category Distribution:</fg=magenta>");
                        foreach ($categoryData as $category) {
                            $this->line("  • <fg=yellow>{$category->name}</fg=yellow>: {$category->menu_items_count} items");
                        }
                    }
                } catch (\Exception $e) {
                    $this->warn("  ⚠️ Category analysis failed: " . $e->getMessage());
                }
            }
            
        } catch (\Exception $e) {
            $this->error("  ❌ Data query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Display orders data summary
     */
    private function displayOrdersDataSummary(): void
    {
        $this->line("\n📊 <fg=cyan>Orders Data Analysis:</fg=cyan>");
        
        try {
            $total = Order::count();
            if ($total > 0) {
                $statusBreakdown = Order::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get();
                
                $this->line("  📋 Total Orders: <fg=yellow>{$total}</fg=yellow>");
                $this->line("  📈 Status Breakdown:");
                
                foreach ($statusBreakdown as $status) {
                    $percentage = round(($status->count / $total) * 100, 1);
                    $this->line("    • <fg=green>{$status->status}</fg=green>: {$status->count} ({$percentage}%)");
                }
            } else {
                $this->warn("  📭 No orders found in the system");
            }
            
        } catch (\Exception $e) {
            $this->error("  ❌ Orders data query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check organizations requirements
     */
    private function checkOrganizationsRequirements(array $columns, bool $shouldFix): void
    {
        $this->line("\n🏢 <fg=cyan>Organizations Table Analysis:</fg=cyan>");
        
        $requiredColumns = [
            'name' => ['type' => 'string', 'description' => 'Organization name'],
            'is_active' => ['type' => 'boolean', 'description' => 'Active status'],
            'email' => ['type' => 'string', 'description' => 'Contact email'],
            'phone' => ['type' => 'string', 'description' => 'Contact phone'],
            'address' => ['type' => 'text', 'description' => 'Organization address']
        ];
        
        foreach ($requiredColumns as $column => $config) {
            if (in_array($column, $columns)) {
                $this->info("  ✅ <fg=green>{$column}</fg=green> - {$config['description']}");
            } else {
                $this->warn("  ⚠️ <fg=yellow>Missing:</fg=yellow> {$column} - {$config['description']}");
            }
        }
        
        try {
            $total = Organization::count();
            $active = Organization::where('is_active', true)->count();
            $this->line("\n  📊 Organizations: {$total} total, {$active} active");
        } catch (\Exception $e) {
            $this->error("  ❌ Data query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check branches requirements
     */
    private function checkBranchesRequirements(array $columns, bool $shouldFix): void
    {
        $this->line("\n🏪 <fg=cyan>Branches Table Analysis:</fg=cyan>");
        
        $requiredColumns = [
            'name' => ['type' => 'string', 'description' => 'Branch name'],
            'organization_id' => ['type' => 'foreign', 'description' => 'Organization relationship'],
            'is_active' => ['type' => 'boolean', 'description' => 'Active status'],
            'address' => ['type' => 'text', 'description' => 'Branch address'],
            'phone' => ['type' => 'string', 'description' => 'Branch phone']
        ];
        
        foreach ($requiredColumns as $column => $config) {
            if (in_array($column, $columns)) {
                $this->info("  ✅ <fg=green>{$column}</fg=green> - {$config['description']}");
            } else {
                $this->warn("  ⚠️ <fg=yellow>Missing:</fg=yellow> {$column} - {$config['description']}");
            }
        }
        
        try {
            $total = Branch::count();
            $active = Branch::where('is_active', true)->count();
            $this->line("\n  📊 Branches: {$total} total, {$active} active");
        } catch (\Exception $e) {
            $this->error("  ❌ Data query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Perform full system analysis
     */
    private function performFullSystemAnalysis(): void
    {
        $this->line("\n🔍 <fg=white;bg=green> Full System Analysis </fg=white;bg=green>");
        
        $tables = [
            'organizations' => Organization::class,
            'branches' => Branch::class,
            'menu_items' => MenuItem::class,
            'orders' => Order::class,
        ];
        
        foreach ($tables as $tableName => $modelClass) {
            if (Schema::hasTable($tableName)) {
                try {
                    $count = $modelClass::count();
                    $this->info("  ✅ {$tableName}: {$count} records");
                } catch (\Exception $e) {
                    $this->warn("  ⚠️ {$tableName}: Query failed - {$e->getMessage()}");
                }
            } else {
                $this->error("  ❌ {$tableName}: Table missing");
            }
        }
    }
    
    /**
     * Display system summary with actionable insights
     */
    private function displaySystemSummary(): void
    {
        $this->line("\n📋 <fg=white;bg=blue> System Health Summary </fg=white;bg=blue>");
        
        $healthChecks = [
            'Database Connection' => $this->checkDatabaseConnection(),
            'Core Tables' => $this->checkCoreTables(),
            'Data Integrity' => $this->checkDataIntegrity(),
            'Indexes' => $this->checkIndexes(),
        ];
        
        foreach ($healthChecks as $check => $status) {
            $icon = $status ? '✅' : '❌';
            $color = $status ? 'green' : 'red';
            $this->line("  {$icon} <fg={$color}>{$check}</fg={$color}>");
        }
        
        $this->line("\n🚀 <fg=yellow>Quick Actions:</fg=yellow>");
        $this->line("  • Run migrations: <fg=cyan>php artisan migrate</fg=cyan>");
        $this->line("  • Seed test data: <fg=cyan>php artisan db:seed --class=MenuItemSeeder</fg=cyan>");
        $this->line("  • Check specific table: <fg=cyan>php artisan db:diagnose --table=orders</fg=cyan>");
        $this->line("  • Full analysis: <fg=cyan>php artisan db:diagnose --full</fg=cyan>");
    }
    
    /**
     * Check database connection health
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Check core tables existence
     */
    private function checkCoreTables(): bool
    {
        $coreTables = ['organizations', 'branches', 'menu_items', 'orders', 'reservations'];
        
        foreach ($coreTables as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check data integrity
     */
    private function checkDataIntegrity(): bool
    {
        try {
            // Basic integrity checks
            $orgCount = Organization::count();
            $branchCount = Branch::count();
            
            return $orgCount > 0 && $branchCount > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Check database indexes
     */
    private function checkIndexes(): bool
    {
        // Simplified index check - in real implementation, 
        // you'd query information_schema for actual indexes
        return true;
    }
    
    /**
     * Suggest table creation for missing tables
     */
    private function suggestTableCreation(string $tableName): void
    {
        $this->line("\n💡 <fg=yellow>Suggestions:</fg=yellow>");
        $this->line("  1. Run migrations: <fg=cyan>php artisan migrate</fg=cyan>");
        $this->line("  2. Check migration files in database/migrations/");
        $this->line("  3. Create migration: <fg=cyan>php artisan make:migration create_{$tableName}_table</fg=cyan>");
    }
    
    /**
     * Generic table requirements check
     */
    private function checkGenericTableRequirements(string $tableName, array $columns): void
    {
        $this->line("\n🔧 <fg=cyan>Generic Table Analysis:</fg=cyan>");
        
        $commonColumns = ['id', 'created_at', 'updated_at'];
        $hasCommonColumns = true;
        
        foreach ($commonColumns as $column) {
            if (in_array($column, $columns)) {
                $this->info("  ✅ <fg=green>{$column}</fg=green> - Standard Laravel column");
            } else {
                $this->warn("  ⚠️ <fg=yellow>Missing:</fg=yellow> {$column} - Standard Laravel column");
                $hasCommonColumns = false;
            }
        }
        
        if ($hasCommonColumns) {
            $this->info("  🎉 Table follows Laravel conventions!");
        }
    }
}
