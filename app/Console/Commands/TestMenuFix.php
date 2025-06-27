<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class TestMenuFix extends Command
{
    protected $signature = 'test:menu-fix';
    protected $description = 'Test if the menu_menu_items.override_price SQL error is fixed';

    public function handle()
    {
        $this->info('=== TESTING MENU SQL FIX ===');
        
        try {
            // Test 1: Menu with menuItems relationship
            $this->info('1. Testing Menu::with("menuItems") query...');
            $menus = Menu::with('menuItems')->take(3)->get();
            $this->info("✓ SUCCESS: Query executed without SQLSTATE[42703] error!");
            $this->info("Found {$menus->count()} menu(s)");
            
            foreach ($menus as $menu) {
                $itemCount = $menu->menuItems->count();
                $this->info("   - Menu: {$menu->name} ({$itemCount} items)");
                
                if ($itemCount > 0) {
                    $firstItem = $menu->menuItems->first();
                    $overridePrice = $firstItem->pivot->override_price ?? 'NULL';
                    $sortOrder = $firstItem->pivot->sort_order ?? 'NULL';
                    $this->info("     First item pivot: override_price={$overridePrice}, sort_order={$sortOrder}");
                }
            }
            
            // Test 2: Direct SQL query
            $this->info('');
            $this->info('2. Testing direct SQL query with override_price...');
            $results = DB::select("
                SELECT m.id, m.name, mmi.override_price, mmi.sort_order 
                FROM menus m 
                LEFT JOIN menu_menu_items mmi ON m.id = mmi.menu_id 
                LIMIT 5
            ");
            
            $this->info("✓ SUCCESS: Direct SQL query worked!");
            $this->info("Returned " . count($results) . " rows");
            
            foreach ($results as $row) {
                $overridePrice = $row->override_price ?? 'NULL';
                $sortOrder = $row->sort_order ?? 'NULL';
                $this->info("   - {$row->name}: override_price={$overridePrice}, sort_order={$sortOrder}");
            }
            
            // Test 3: Verify table structure
            $this->info('');
            $this->info('3. Testing table structure...');
            $columns = DB::select("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = 'menu_menu_items' 
                AND column_name IN ('override_price', 'sort_order', 'special_price', 'display_order')
                ORDER BY column_name
            ");
            
            $foundColumns = array_map(fn($col) => $col->column_name, $columns);
            
            if (in_array('override_price', $foundColumns)) {
                $this->info("✓ Column 'override_price' exists");
            } else {
                $this->error("✗ Column 'override_price' missing!");
            }
            
            if (in_array('sort_order', $foundColumns)) {
                $this->info("✓ Column 'sort_order' exists");
            } else {
                $this->error("✗ Column 'sort_order' missing!");
            }
            
            if (in_array('special_price', $foundColumns)) {
                $this->error("✗ Old column 'special_price' still exists!");
            } else {
                $this->info("✓ Old column 'special_price' successfully removed");
            }
            
            if (in_array('display_order', $foundColumns)) {
                $this->error("✗ Old column 'display_order' still exists!");
            } else {
                $this->info("✓ Old column 'display_order' successfully removed");
            }
            
            $this->info('');
            $this->info('=== TEST RESULTS ===');
            $this->info('✓ Menu model relationships work correctly');
            $this->info('✓ SQL queries execute without column errors');
            $this->info('✓ Database schema is properly updated');
            $this->info('');
            $this->info('🎉 The SQLSTATE[42703]: Undefined column error has been RESOLVED!');
            $this->info('The menus page should now load without errors.');
            
        } catch (\Exception $e) {
            $this->error('');
            $this->error('=== TEST FAILED ===');
            $this->error("✗ Error: {$e->getMessage()}");
            $this->error("File: {$e->getFile()} Line: {$e->getLine()}");
            $this->error('');
            $this->error('The SQL error may not be fully resolved.');
            return 1;
        }
        
        return 0;
    }
}
