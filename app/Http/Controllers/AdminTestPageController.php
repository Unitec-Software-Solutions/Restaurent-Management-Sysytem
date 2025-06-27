<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AdminTestPageController extends Controller
{
    /**
     * Display the admin test page
     * Following UI/UX guidelines for test page functionality
     */
    public function index()
    {
        // Get available routes for testing
        $testRoutes = $this->getTestRoutes();
        
        return view('admin.testpage', compact('testRoutes'));
    }

    /**
     * Get test routes organized by category
     * Following UI/UX guidelines for categorized display
     */
    private function getTestRoutes(): array
    {
        $routes = [
            'admin_functions' => [
                'title' => '🛠️ Admin Functions',
                'description' => 'Administrative operations for managing reservations, orders, inventory, and users.',
                'routes' => [
                    ['name' => 'Reservations', 'route' => 'admin.reservations.index', 'icon' => 'fa-calendar-check'],
                    ['name' => 'Orders (Admin)', 'route' => 'admin.orders.index', 'icon' => 'fa-receipt'],
                    ['name' => 'Create Takeaway', 'route' => 'admin.orders.takeaway.create', 'icon' => 'fa-shopping-bag'],
                    ['name' => 'Inventory Dashboard', 'route' => 'admin.inventory.index', 'icon' => 'fa-boxes'],
                    ['name' => 'Items', 'route' => 'admin.inventory.items.index', 'icon' => 'fa-list'],
                    ['name' => 'Add Item', 'route' => 'admin.inventory.items.create', 'icon' => 'fa-plus'],
                    ['name' => 'Stock Transactions', 'route' => 'admin.inventory.stock.index', 'icon' => 'fa-exchange-alt'],
                    ['name' => 'Suppliers', 'route' => 'admin.suppliers.index', 'icon' => 'fa-truck'],
                    ['name' => 'Add Supplier', 'route' => 'admin.suppliers.create', 'icon' => 'fa-plus-circle'],
                    ['name' => 'Profile', 'route' => 'admin.profile.index', 'icon' => 'fa-user'],
                ]
            ],
            'public_functions' => [
                'title' => '🌐 Public Functions',
                'description' => 'Customer-facing functionality and public interfaces.',
                'routes' => [
                    ['name' => 'Home', 'route' => 'home', 'icon' => 'fa-home'],
                    ['name' => 'Customer Dashboard', 'route' => 'customer.dashboard', 'icon' => 'fa-tachometer-alt'],
                    ['name' => 'Create Reservation', 'route' => 'reservations.create', 'icon' => 'fa-calendar-plus'],
                    ['name' => 'Create Order', 'route' => 'orders.create', 'icon' => 'fa-shopping-cart'],
                ]
            ],
            'sample_pages' => [
                'title' => '🧪 Admin Sample Pages',
                'description' => 'Sample pages for UI demonstration - no backend logic.',
                'routes' => [
                    ['name' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'fa-tachometer-alt'],
                    ['name' => 'Settings', 'route' => 'admin.settings.index', 'icon' => 'fa-cog'],
                    ['name' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'fa-chart-bar'],
                ]
            ]
        ];

        // Check route availability for each category
        foreach ($routes as &$category) {
            foreach ($category['routes'] as &$route) {
                $route['available'] = Route::has($route['route']);
                $route['url'] = $route['available'] ? route($route['route']) : '#';
            }
        }

        return $routes;
    }
}
