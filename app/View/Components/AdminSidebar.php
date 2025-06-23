<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class AdminSidebar extends Component
{
    public function __construct()
    {
        //
    }

    public function render()
    {
        return view('components.admin-sidebar', [
            'menuItems' => $this->getMenuItems(),
            'currentUser' => Auth::guard('admin')->user(),
        ]);
    }

    private function getMenuItems()
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return [];
        }

        $menuItems = [
            [
                'title' => 'Dashboard',
                'route' => 'admin.dashboard',
                'icon' => 'layout-dashboard',
                'icon_type' => 'svg',
                'permission' => null,
                'badge' => 0,
                'sub_items' => []
            ],
            [
                'title' => 'Inventory',
                'route' => 'admin.inventory.index',
                'icon' => 'package',
                'icon_type' => 'svg',
                'permission' => 'inventory.view',
                'badge' => 0,
                'sub_items' => [
                    [
                        'title' => 'Dashboard',
                        'route' => 'admin.inventory.dashboard',
                        'icon' => 'layout-dashboard',
                        'icon_type' => 'svg',
                        'permission' => 'inventory.view'
                    ],
                    [
                        'title' => 'Items',
                        'route' => 'admin.inventory.items.index',
                        'icon' => 'box',
                        'icon_type' => 'svg',
                        'permission' => 'inventory.view'
                    ],
                    [
                        'title' => 'Categories',
                        'route' => 'admin.inventory.categories.index',
                        'icon' => 'tag',
                        'icon_type' => 'svg',
                        'permission' => 'inventory.view'
                    ],
                    [
                        'title' => 'Stock',
                        'route' => 'admin.inventory.stock.index',
                        'icon' => 'bar-chart',
                        'icon_type' => 'svg',
                        'permission' => 'inventory.view'
                    ]
                ]
            ],
            [
                'title' => 'Orders',
                'route' => 'admin.orders.index',
                'icon' => 'shopping-cart',
                'icon_type' => 'svg',
                'permission' => 'orders.view',
                'badge' => $this->getPendingOrdersCount(),
                'sub_items' => [
                    [
                        'title' => 'All Orders',
                        'route' => 'admin.orders.index',
                        'icon' => 'list',
                        'icon_type' => 'svg',
                        'permission' => 'orders.view'
                    ],
                    [
                        'title' => 'Takeaway',
                        'route' => 'admin.orders.takeaway.index',
                        'icon' => 'shopping-bag',
                        'icon_type' => 'svg',
                        'permission' => 'orders.view'
                    ]
                ]
            ],
            [
                'title' => 'Reservations',
                'route' => 'admin.reservations.index',
                'icon' => 'calendar-clock',
                'icon_type' => 'svg',
                'permission' => 'reservations.view',
                'badge' => 0,
                'sub_items' => []
            ],
            [
                'title' => 'Menu Management',
                'route' => 'admin.menus.index',
                'icon' => 'utensils',
                'icon_type' => 'svg',
                'permission' => 'menus.view',
                'badge' => 0,
                'sub_items' => [
                    [
                        'title' => 'All Menus',
                        'route' => 'admin.menus.list',
                        'icon' => 'list',
                        'icon_type' => 'svg',
                        'permission' => 'menus.view'
                    ],
                    [
                        'title' => 'Calendar View',
                        'route' => 'admin.menus.calendar',
                        'icon' => 'calendar',
                        'icon_type' => 'svg',
                        'permission' => 'menus.view'
                    ],
                    [
                        'title' => 'Create Menu',
                        'route' => 'admin.menus.create',
                        'icon' => 'plus',
                        'icon_type' => 'svg',
                        'permission' => 'menus.create'
                    ],
                    [
                        'title' => 'Safety Dashboard',
                        'route' => 'admin.menus.safety-dashboard',
                        'icon' => 'shield-alt',
                        'icon_type' => 'svg',
                        'permission' => 'menus.view'
                    ]
                ]
            ],
            [
                'title' => 'Customers',
                'route' => 'admin.customers.index',
                'icon' => 'users',
                'icon_type' => 'svg',
                'permission' => 'customers.view',
                'badge' => 0,
                'sub_items' => []
            ],
            [
                'title' => 'Suppliers',
                'route' => 'admin.suppliers.index',
                'icon' => 'truck',
                'icon_type' => 'svg',
                'permission' => 'suppliers.view',
                'badge' => 0,
                'sub_items' => []
            ],
            [
                'title' => 'Reports',
                'route' => 'admin.reports.index',
                'icon' => 'bar-chart-3',
                'icon_type' => 'svg',
                'permission' => 'reports.view',
                'badge' => 0,
                'sub_items' => []
            ]
        ];

        // Add organization management for super admins
        if ($admin->is_super_admin) {
            $menuItems[] = [
                'title' => 'Organizations',
                'route' => 'admin.organizations.index',
                'icon' => 'building',
                'icon_type' => 'svg',
                'permission' => null,
                'badge' => 0,
                'sub_items' => [
                    [
                        'title' => 'Activate Organization',
                        'route' => 'admin.organizations.activate.form',
                        'icon' => 'key',
                        'icon_type' => 'svg',
                        'permission' => null
                    ]
                ]
            ];

            $menuItems[] = [
                'title' => 'Subscription Plans',
                'route' => 'admin.subscription-plans.index',
                'icon' => 'credit-card',
                'icon_type' => 'svg',
                'permission' => null,
                'badge' => 0,
                'sub_items' => []
            ];

            $menuItems[] = [
                'title' => 'Roles & Permissions',
                'route' => 'admin.roles.index',
                'icon' => 'lock',
                'icon_type' => 'svg',
                'permission' => null,
                'badge' => 0,
                'sub_items' => []
            ];

            $menuItems[] = [
                'title' => 'Modules Management',
                'route' => 'admin.modules.index',
                'icon' => 'cogs',
                'icon_type' => 'svg',
                'permission' => null,
                'badge' => 0,
                'sub_items' => []
            ];
        }

        // Add branch management
        $branchRoute = $admin->is_super_admin ? 'admin.branches.global' : 'admin.branches.index';
        $branchParams = $admin->is_super_admin ? [] : ['organization' => $admin->organization_id];
        
        if ($admin->organization_id || $admin->is_super_admin) {
            $menuItems[] = [
                'title' => 'Branches',
                'route' => $branchRoute,
                'route_params' => $branchParams,
                'icon' => 'store',
                'icon_type' => 'svg',
                'permission' => 'branches.view',
                'badge' => 0,
                'sub_items' => [
                    [
                        'title' => 'Activate Branch',
                        'route' => 'admin.branches.activate.form',
                        'icon' => 'key',
                        'icon_type' => 'svg',
                        'permission' => 'branches.activate'
                    ]
                ]
            ];
        }

        // Add user management
        $menuItems[] = [
            'title' => 'Users',
            'route' => 'admin.users.index',
            'icon' => 'user-friends',
            'icon_type' => 'svg',
            'permission' => 'users.view',
            'badge' => 0,
            'sub_items' => []
        ];

        return collect($menuItems)->filter(function ($item) {
            return $this->hasPermission($item) && $this->routeExists($item);
        })->toArray();
    }

    private function hasPermission($item)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return false;
        }

        if ($admin->is_super_admin) {
            return true;
        }

        if (!isset($item['permission']) || $item['permission'] === null) {
            return true;
        }

        // Check if admin has permission (implement your permission logic here)
        return $admin->hasPermission($item['permission']) ?? true;
    }

    private function routeExists($item)
    {
        return Route::has($item['route']);
    }

    private function getPendingOrdersCount()
    {
        try {
            // Implement your pending orders count logic here
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
