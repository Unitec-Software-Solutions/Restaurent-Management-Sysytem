<?php

use Illuminate\Support\Facades\Route;
use App\Models\Menu;

Route::get('/test-menu-fix', function () {
    try {
        // Test the exact query that was failing
        $menus = Menu::with('menuItems')->get();
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Menu Fix Test - SUCCESS</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .success { color: green; font-weight: bold; }
                .info { color: #333; margin: 10px 0; }
                .menu-item { background: #f5f5f5; padding: 10px; margin: 5px 0; border-radius: 5px; }
            </style>
        </head>
        <body>
            <h1 class="success">✓ SQL Fix Verification - SUCCESS!</h1>
            <p class="info">The SQLSTATE[42703]: Undefined column: menu_menu_items.override_price error has been resolved.</p>
            
            <h2>Test Results:</h2>
            <div class="menu-item">
                <strong>Query:</strong> Menu::with("menuItems")->get()<br>
                <strong>Status:</strong> <span class="success">SUCCESS</span><br>
                <strong>Menus Found:</strong> ' . $menus->count() . '<br>
                <strong>Database Schema:</strong> Updated with override_price and sort_order columns
            </div>
            
            <h2>Menus in Database:</h2>';
            
        foreach ($menus as $menu) {
            $html .= '
            <div class="menu-item">
                <strong>Menu:</strong> ' . htmlspecialchars($menu->name) . '<br>
                <strong>Items:</strong> ' . $menu->menuItems->count() . '<br>
                <strong>Active:</strong> ' . ($menu->is_active ? 'Yes' : 'No') . '<br>
                <strong>Type:</strong> ' . htmlspecialchars($menu->menu_type ?? 'N/A') . '
            </div>';
        }
        
        $html .= '
            <h2>Next Steps:</h2>
            <p class="info">✓ The SQL error is resolved. The admin menus page should now load without errors.</p>
            <p class="info">✓ You can now safely access: <a href="/admin/menus" target="_blank">/admin/menus</a></p>
            <p class="info">✓ All menu-related functionality should work correctly.</p>
        </body>
        </html>';
        
        return $html;
        
    } catch (Exception $e) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Menu Fix Test - ERROR</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .error { color: red; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1 class="error">✗ SQL Fix Test - ERROR</h1>
            <p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
            <p>The fix may not be complete. Please check the database schema and migrations.</p>
        </body>
        </html>';
    }
});
