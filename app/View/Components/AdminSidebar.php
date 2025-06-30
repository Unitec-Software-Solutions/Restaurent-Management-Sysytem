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

    /**
     * Real-time badge count methods
     */
    private function getDashboardNotificationCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;
        
        $count = 0;
        
        // Add pending orders
        $count += $this->getPendingOrdersCount();
        
        // Add low stock items
        $count += $this->getLowStockItemsCount();
        
        // Add today's reservations needing attention
        $count += $this->getPendingReservationsCount();
        
        return min($count, 99); // Cap at 99 for display
    }

    private function getPendingOrdersCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;
        
        try {
            $query = \App\Models\Order::whereIn('status', ['pending', 'confirmed', 'preparing']);
            
            // Apply scope restrictions
            if (!$admin->is_super_admin) {
                if ($admin->branch_id) {
                    $query->where('branch_id', $admin->branch_id);
                } elseif ($admin->organization_id) {
                    $query->whereHas('branch', function ($q) use ($admin) {
                        $q->where('organization_id', $admin->organization_id);
                    });
                }
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getPendingOrganizationsCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || !$admin->is_super_admin) return 0;
        
        try {
            return \App\Models\Organization::where('status', 'pending')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getActiveBranchesCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;
        
        try {
            $query = \App\Models\Branch::where('is_active', true);
            
            if (!$admin->is_super_admin && $admin->organization_id) {
                $query->where('organization_id', $admin->organization_id);
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getActiveMenusCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;
        
        try {
            $query = \App\Models\Menu::where('is_active', true);
            
            if (!$admin->is_super_admin) {
                if ($admin->branch_id) {
                    $query->where('branch_id', $admin->branch_id);
                } elseif ($admin->organization_id) {
                    $query->whereHas('branch', function ($q) use ($admin) {
                        $q->where('organization_id', $admin->organization_id);
                    });
                }
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getLowStockItemsCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;
        
        try {
            $query = \App\Models\InventoryItem::whereRaw('current_stock <= reorder_level');
            
            if (!$admin->is_super_admin) {
                if ($admin->branch_id) {
                    $query->where('branch_id', $admin->branch_id);
                } elseif ($admin->organization_id) {
                    $query->where('organization_id', $admin->organization_id);
                }
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTodayReservationsCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;
        
        try {
            $query = \App\Models\Reservation::whereDate('reservation_date', today())
                ->whereIn('status', ['confirmed', 'pending']);
            
            if (!$admin->is_super_admin) {
                if ($admin->branch_id) {
                    $query->where('branch_id', $admin->branch_id);
                } elseif ($admin->organization_id) {
                    $query->whereHas('branch', function ($q) use ($admin) {
                        $q->where('organization_id', $admin->organization_id);
                    });
                }
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getPendingReservationsCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;
        
        try {
            $query = \App\Models\Reservation::where('status', 'pending');
            
            if (!$admin->is_super_admin) {
                if ($admin->branch_id) {
                    $query->where('branch_id', $admin->branch_id);
                } elseif ($admin->organization_id) {
                    $query->whereHas('branch', function ($q) use ($admin) {
                        $q->where('organization_id', $admin->organization_id);
                    });
                }
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getActiveStaffCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;
        
        try {
            $query = \App\Models\Admin::where('is_active', true)
                ->where('is_super_admin', false);
            
            if (!$admin->is_super_admin && $admin->organization_id) {
                $query->where('organization_id', $admin->organization_id);
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getActiveKOTsCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;
        
        try {
            $query = \App\Models\Kot::whereIn('status', ['pending', 'started', 'cooking']);
            
            if (!$admin->is_super_admin) {
                $query->whereHas('order', function ($q) use ($admin) {
                    if ($admin->branch_id) {
                        $q->where('branch_id', $admin->branch_id);
                    } elseif ($admin->organization_id) {
                        $q->whereHas('branch', function ($subQ) use ($admin) {
                            $subQ->where('organization_id', $admin->organization_id);
                        });
                    }
                });
            }
            
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Update the render method to use enhanced menu items
     */
    public function render()
    {
        return view('components.admin-sidebar', [
            'menuItems' => $this->getMenuItemsEnhanced(),
            'currentUser' => Auth::guard('admin')->user(),
            'sidebarState' => $this->getSidebarState(),
        ]);
    }

    /**
     * Get sidebar state (collapsed/expanded) from user preferences
     */
    private function getSidebarState(): array
    {
        $admin = Auth::guard('admin')->user();
        
        $defaultState = [
            'collapsed' => false,
            'theme' => 'light',
            'show_badges' => true,
            'auto_collapse_mobile' => true
        ];
        
        if (!$admin || !isset($admin->ui_settings)) {
            return $defaultState;
        }
        
        $uiSettings = is_string($admin->ui_settings) 
            ? json_decode($admin->ui_settings, true) 
            : $admin->ui_settings;
            
        return array_merge($defaultState, $uiSettings['sidebar'] ?? []);
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
                        'title' => 'Create Order',
                        'route' => 'admin.orders.create',
                        'icon' => 'plus-circle',
                        'icon_type' => 'svg',
                        'permission' => 'orders.create'
                    ],
                    [
                        'title' => 'Dine-In Orders',
                        'route' => 'admin.orders.index',
                        'route_params' => ['type' => 'in_house'],
                        'icon' => 'utensils',
                        'icon_type' => 'svg',
                        'permission' => 'orders.view'
                    ],
                    [
                        'title' => 'Takeaway Orders',
                        'route' => 'admin.orders.index',
                        'route_params' => ['type' => 'takeaway'],
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
                        'title' => 'All Organizations',
                        'route' => 'admin.organizations.index',
                        'icon' => 'list',
                        'icon_type' => 'svg',
                        'permission' => null
                    ],
                    [
                        'title' => 'Create Organization',
                        'route' => 'admin.organizations.create',
                        'icon' => 'plus',
                        'icon_type' => 'svg',
                        'permission' => null
                    ],
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
                        'title' => 'All Branches',
                        'route' => $branchRoute,
                        'route_params' => $branchParams,
                        'icon' => 'list',
                        'icon_type' => 'svg',
                        'permission' => 'branches.view'
                    ],
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

        return $menuItems;
    }

 
    private function getMenuItemsEnhanced()
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return [];
        }

        $menuItems = [];

        // Dashboard - Always visible
        $menuItems[] = [
            'title' => 'Dashboard',
            'route' => 'admin.dashboard',
            'route_params' => [],
            'icon' => 'layout-dashboard',
            'icon_type' => 'svg',
            'permission' => null,
            'badge' => $this->getDashboardNotificationCount(),
            'badge_color' => 'indigo',
            'is_route_valid' => $this->validateRoute('admin.dashboard'),
            'sub_items' => []
        ];

        // Organization Management (Super Admin only)
        if ($admin->is_super_admin) {
            $menuItems[] = [
                'title' => 'Organizations',
                'route' => 'admin.organizations.index',
                'route_params' => [],
                'icon' => 'building-office',
                'icon_type' => 'svg',
                'permission' => 'organizations.view',
                'badge' => $this->getPendingOrganizationsCount(),
                'badge_color' => 'blue',
                'is_route_valid' => $this->validateRoute('admin.organizations.index'),
                'sub_items' => [
                    [
                        'title' => 'All Organizations',
                        'route' => 'admin.organizations.index',
                        'icon' => 'list',
                        'icon_type' => 'svg',
                        'permission' => 'organizations.view',
                        'is_route_valid' => $this->validateRoute('admin.organizations.index')
                    ],
                    [
                        'title' => 'Add Organization',
                        'route' => 'admin.organizations.create',
                        'icon' => 'plus',
                        'icon_type' => 'svg',
                        'permission' => 'organizations.create',
                        'is_route_valid' => $this->validateRoute('admin.organizations.create')
                    ],
                    [
                        'title' => 'Activate Organization',
                        'route' => 'admin.organizations.activate.form',
                        'icon' => 'key',
                        'icon_type' => 'svg',
                        'permission' => 'organizations.activate',
                        'is_route_valid' => $this->validateRoute('admin.organizations.activate.form')
                    ]
                ]
            ];
        }

        // Branches (Organization/Super Admin)
        if ($admin->organization_id || $admin->is_super_admin) {
            $branchRoute = $admin->is_super_admin ? 'admin.branches.global' : 'admin.branches.index';
            $branchParams = $admin->is_super_admin ? [] : ['organization' => $admin->organization_id];
            
            $menuItems[] = [
                'title' => 'Branches',
                'route' => $branchRoute,
                'route_params' => $branchParams,
                'icon' => 'store',
                'icon_type' => 'svg',
                'permission' => 'branches.view',
                'badge' => $this->getActiveBranchesCount(),
                'badge_color' => 'green',
                'is_route_valid' => $this->validateRoute($branchRoute, $branchParams),
                'sub_items' => $this->getBranchSubItems($admin)
            ];
        }

        // Orders with real-time status
        $menuItems[] = [
            'title' => 'Orders',
            'route' => 'admin.orders.index',
            'route_params' => [],
            'icon' => 'shopping-cart',
            'icon_type' => 'svg',
            'permission' => 'orders.view',
            'badge' => $this->getPendingOrdersCount(),
            'badge_color' => 'red',
            'is_route_valid' => $this->validateRoute('admin.orders.index'),
            'sub_items' => $this->getOrderSubItems()
        ];

        // Menu Management
        if ($this->hasPermission($admin, 'menus.view')) {
            $menuItems[] = [
                'title' => 'Menus',
                'route' => 'admin.menus.index',
                'route_params' => [],
                'icon' => 'book-open',
                'icon_type' => 'svg',
                'permission' => 'menus.view',
                'badge' => $this->getActiveMenusCount(),
                'badge_color' => 'yellow',
                'is_route_valid' => $this->validateRoute('admin.menus.index'),
                'sub_items' => $this->getMenuSubItems()
            ];
        }

        // Inventory Management
        if ($this->hasPermission($admin, 'inventory.view')) {
            $menuItems[] = [
                'title' => 'Inventory',
                'route' => 'admin.inventory.index',
                'route_params' => [],
                'icon' => 'package',
                'icon_type' => 'svg',
                'permission' => 'inventory.view',
                'badge' => $this->getLowStockItemsCount(),
                'badge_color' => 'orange',
                'is_route_valid' => $this->validateRoute('admin.inventory.index'),
                'sub_items' => $this->getInventorySubItems()
            ];
        }

        // Suppliers Management (separate from inventory)
        if ($this->hasPermission($admin, 'suppliers.view')) {
            $menuItems[] = [
                'title' => 'Suppliers',
                'route' => 'admin.suppliers.index',
                'route_params' => [],
                'icon' => 'truck',
                'icon_type' => 'svg',
                'permission' => 'suppliers.view',
                'badge' => 0,
                'badge_color' => 'blue',
                'is_route_valid' => $this->validateRoute('admin.suppliers.index'),
                'sub_items' => $this->getSupplierSubItems()
            ];
        }

        // Reservations
        if ($this->hasPermission($admin, 'reservations.view')) {
            $menuItems[] = [
                'title' => 'Reservations',
                'route' => 'admin.reservations.index',
                'route_params' => [],
                'icon' => 'calendar',
                'icon_type' => 'svg',
                'permission' => 'reservations.view',
                'badge' => $this->getTodayReservationsCount(),
                'badge_color' => 'purple',
                'is_route_valid' => $this->validateRoute('admin.reservations.index'),
                'sub_items' => $this->getReservationSubItems()
            ];
        }

        // Staff Management (Admin level and above)
        if ($this->hasPermission($admin, 'users.view') && !$this->isStaffLevel($admin)) {
            $menuItems[] = [
                'title' => 'Staff',
                'route' => 'admin.users.index',
                'route_params' => [],
                'icon' => 'users',
                'icon_type' => 'svg',
                'permission' => 'users.view',
                'badge' => $this->getActiveStaffCount(),
                'badge_color' => 'cyan',
                'is_route_valid' => $this->validateRoute('admin.users.index'),
                'sub_items' => $this->getStaffSubItems()
            ];
        }

        // Reports and Analytics
        if ($this->hasPermission($admin, 'reports.view')) {
            $menuItems[] = [
                'title' => 'Reports',
                'route' => 'admin.reports.index',
                'route_params' => [],
                'icon' => 'chart-bar',
                'icon_type' => 'svg',
                'permission' => 'reports.view',
                'badge' => 0,
                'badge_color' => 'gray',
                'is_route_valid' => $this->validateRoute('admin.reports.index'),
                'sub_items' => $this->getReportSubItems()
            ];
        }

        // Kitchen Operations (for branch staff)
        if ($this->hasPermission($admin, 'kitchen.view')) {
            $menuItems[] = [
                'title' => 'Kitchen',
                'route' => 'admin.kitchen.index',
                'route_params' => [],
                'icon' => 'chef-hat',
                'icon_type' => 'svg',
                'permission' => 'kitchen.view',
                'badge' => $this->getActiveKOTsCount(),
                'badge_color' => 'red',
                'is_route_valid' => $this->validateRoute('admin.kitchen.index'),
                'sub_items' => $this->getKitchenSubItems()
            ];
        }

        // Settings (Admin level and above)
        if (!$this->isStaffLevel($admin)) {
            $menuItems[] = [
                'title' => 'Settings',
                'route' => 'admin.settings.index',
                'route_params' => [],
                'icon' => 'cog',
                'icon_type' => 'svg',
                'permission' => 'settings.view',
                'badge' => 0,
                'badge_color' => 'gray',
                'is_route_valid' => $this->validateRoute('admin.settings.index'),
                'sub_items' => $this->getSettingsSubItems()
            ];
        }

        // Filter out items without valid routes or permissions
        return array_filter($menuItems, function ($item) use ($admin) {
            return $this->isMenuItemAccessible($item, $admin);
        });
    }

    /**
     * Validate if a route exists and is accessible
     */
    private function validateRoute(string $routeName, array $params = []): bool
    {
        try {
            if (!Route::has($routeName)) {
                return false;
            }
            
            // Try to generate the route URL to ensure parameters are valid
            route($routeName, $params);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if admin has specific permission
     */
    private function hasPermission($admin, string $permission): bool
    {
        if (!$admin) {
            return false;
        }
        
        // Super admins have all permissions
        if ($admin->is_super_admin) {
            return true;
        }
        
        // For now, allow all authenticated admin users to access inventory and suppliers
        // TODO: Implement proper permission checking later
        if (in_array($permission, ['inventory.view', 'inventory.manage', 'suppliers.view', 'suppliers.manage'])) {
            return true;
        }
        
        // Check using Spatie permissions if admin has hasPermissionTo method
        if (method_exists($admin, 'hasPermissionTo')) {
            return $admin->hasPermissionTo($permission);
        }
        
        // Fallback to basic permission check
        if (method_exists($admin, 'hasPermission')) {
            return $admin->hasPermission($permission);
        }
        
        return false;
    }

    /**
     * Check if admin is staff level (lowest access)
     */
    private function isStaffLevel($admin): bool
    {
        if (!$admin) {
            return true;
        }
        
        if ($admin->is_super_admin) {
            return false;
        }
        
        // Check if admin has organization or branch admin roles
        if ($admin->hasRole(['Admin', 'Organization Admin', 'Branch Admin', 'Branch Manager'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if menu item is accessible by admin
     */
    private function isMenuItemAccessible(array $item, $admin): bool
    {
        // Check route validity
        if (!($item['is_route_valid'] ?? true)) {
            return false;
        }
        
        // Check permission
        if ($item['permission'] && !$this->hasPermission($admin, $item['permission'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Get branch sub-items based on admin level
     */
    private function getBranchSubItems($admin): array
    {
        $subItems = [];
        
        if ($this->hasPermission($admin, 'branches.view')) {
            $listRoute = $admin->is_super_admin ? 'admin.branches.global' : 'admin.branches.index';
            $listParams = $admin->is_super_admin ? [] : ['organization' => $admin->organization_id];
            
            $subItems[] = [
                'title' => 'All Branches',
                'route' => $listRoute,
                'route_params' => $listParams,
                'icon' => 'list',
                'icon_type' => 'svg',
                'permission' => 'branches.view',
                'is_route_valid' => $this->validateRoute($listRoute, $listParams)
            ];
        }
        
        if ($this->hasPermission($admin, 'branches.create')) {
            // All admins need organization parameter for branch creation
            // Super admins can choose which organization, regular admins use their own
            $createRoute = 'admin.branches.create';
            $organizationId = $admin->is_super_admin 
                ? ($admin->organization_id ?? null) // Use current org or null for super admin
                : $admin->organization_id; // Regular admin must use their org
            
            // Only show the link if we have an organization context
            if ($organizationId) {
                $createParams = ['organization' => $organizationId];
                
                $subItems[] = [
                    'title' => 'Add Branch',
                    'route' => $createRoute,
                    'route_params' => $createParams,
                    'icon' => 'plus',
                    'icon_type' => 'svg',
                    'permission' => 'branches.create',
                    'is_route_valid' => $this->validateRoute($createRoute, $createParams)
                ];
            }
        }
        
        // Add branch activation option
        if ($this->hasPermission($admin, 'branches.activate')) {
            $subItems[] = [
                'title' => 'Activate Branch',
                'route' => 'admin.branches.activate.form',
                'route_params' => [],
                'icon' => 'key',
                'icon_type' => 'svg',
                'permission' => 'branches.activate',
                'is_route_valid' => $this->validateRoute('admin.branches.activate.form')
            ];
        }
        
        return $subItems;
    }

    /**
     * Get order sub-items - unified order flow
     */
    private function getOrderSubItems(): array
    {
        return [
            [
                'title' => 'All Orders',
                'route' => 'admin.orders.index',
                'icon' => 'list',
                'icon_type' => 'svg',
                'permission' => 'orders.view',
                'is_route_valid' => $this->validateRoute('admin.orders.index')
            ],
            [
                'title' => 'Create Order',
                'route' => 'admin.orders.create',
                'icon' => 'plus-circle',
                'icon_type' => 'svg',
                'permission' => 'orders.create',
                'is_route_valid' => $this->validateRoute('admin.orders.create')
            ],
            [
                'title' => 'Dine-In Orders',
                'route' => 'admin.orders.index',
                'route_params' => ['type' => 'in_house'],
                'icon' => 'utensils',
                'icon_type' => 'svg',
                'permission' => 'orders.view',
                'is_route_valid' => $this->validateRoute('admin.orders.index')
            ],
            [
                'title' => 'Takeaway Orders',
                'route' => 'admin.orders.index',
                'route_params' => ['type' => 'takeaway'],
                'icon' => 'shopping-bag',
                'icon_type' => 'svg',
                'permission' => 'orders.view',
                'is_route_valid' => $this->validateRoute('admin.orders.index')
            ]
        ];
    }

    /**
     * Get menu sub-items
     */
    private function getMenuSubItems(): array
    {
        return [
            [
                'title' => 'All Menus',
                'route' => 'admin.menus.index',
                'icon' => 'list',
                'icon_type' => 'svg',
                'permission' => 'menus.view',
                'is_route_valid' => $this->validateRoute('admin.menus.index')
            ],
            [
                'title' => 'Menu Items',
                'route' => 'admin.menu-items.index',
                'icon' => 'utensils',
                'icon_type' => 'svg',
                'permission' => 'menus.view',
                'is_route_valid' => $this->validateRoute('admin.menu-items.index')
            ],
            [
                'title' => 'Categories',
                'route' => 'admin.menu-categories.index',
                'icon' => 'tag',
                'icon_type' => 'svg',
                'permission' => 'menus.view',
                'is_route_valid' => $this->validateRoute('admin.menu-categories.index')
            ]
        ];
    }

    /**
     * Get inventory sub-items
     */
    private function getInventorySubItems(): array
    {
        return [
            [
                'title' => 'Stock Levels',
                'route' => 'admin.inventory.index',
                'icon' => 'box',
                'icon_type' => 'svg',
                'permission' => 'inventory.view',
                'is_route_valid' => $this->validateRoute('admin.inventory.index')
            ],
            [
                'title' => 'Items Management',
                'route' => 'admin.inventory.items.index',
                'icon' => 'package',
                'icon_type' => 'svg',
                'permission' => 'inventory.view',
                'is_route_valid' => $this->validateRoute('admin.inventory.items.index')
            ],
            [
                'title' => 'Suppliers',
                'route' => 'admin.suppliers.index',
                'icon' => 'truck',
                'icon_type' => 'svg',
                'permission' => 'suppliers.view',
                'is_route_valid' => $this->validateRoute('admin.suppliers.index')
            ],
            [
                'title' => 'Purchase Orders (GRN)',
                'route' => 'admin.grn.index',
                'icon' => 'receipt',
                'icon_type' => 'svg',
                'permission' => 'inventory.view',
                'is_route_valid' => $this->validateRoute('admin.grn.index')
            ]
        ];
    }

    /**
     * Get supplier sub-items
     */
    private function getSupplierSubItems(): array
    {
        return [
            [
                'title' => 'All Suppliers',
                'route' => 'admin.suppliers.index',
                'icon' => 'list',
                'icon_type' => 'svg',
                'permission' => 'suppliers.view',
                'is_route_valid' => $this->validateRoute('admin.suppliers.index')
            ],
            [
                'title' => 'Add Supplier',
                'route' => 'admin.suppliers.create',
                'icon' => 'plus',
                'icon_type' => 'svg',
                'permission' => 'suppliers.create',
                'is_route_valid' => $this->validateRoute('admin.suppliers.create')
            ]
        ];
    }

    /**
     * Get reservation sub-items
     */
    private function getReservationSubItems(): array
    {
        return [
            [
                'title' => 'All Reservations',
                'route' => 'admin.reservations.index',
                'icon' => 'list',
                'icon_type' => 'svg',
                'permission' => 'reservations.view',
                'is_route_valid' => $this->validateRoute('admin.reservations.index')
            ],
            [
                'title' => 'Today\'s Reservations',
                'route' => 'admin.reservations.today',
                'icon' => 'calendar-day',
                'icon_type' => 'svg',
                'permission' => 'reservations.view',
                'is_route_valid' => $this->validateRoute('admin.reservations.today')
            ]
        ];
    }

    /**
     * Get staff sub-items
     */
    private function getStaffSubItems(): array
    {
        return [
            [
                'title' => 'All Staff',
                'route' => 'admin.users.index',
                'icon' => 'users',
                'icon_type' => 'svg',
                'permission' => 'users.view',
                'is_route_valid' => $this->validateRoute('admin.users.index')
            ],
            [
                'title' => 'Add Staff',
                'route' => 'admin.users.create',
                'icon' => 'user-plus',
                'icon_type' => 'svg',
                'permission' => 'users.create',
                'is_route_valid' => $this->validateRoute('admin.users.create')
            ],
            [
                'title' => 'Roles & Permissions',
                'route' => 'admin.roles.index',
                'icon' => 'shield',
                'icon_type' => 'svg',
                'permission' => 'roles.view',
                'is_route_valid' => $this->validateRoute('admin.roles.index')
            ]
        ];
    }

    /**
     * Get kitchen sub-items
     */
    private function getKitchenSubItems(): array
    {
        return [
            [
                'title' => 'Active KOTs',
                'route' => 'admin.kitchen.kots',
                'icon' => 'receipt',
                'icon_type' => 'svg',
                'permission' => 'kitchen.view',
                'is_route_valid' => $this->validateRoute('admin.kitchen.kots')
            ],
            [
                'title' => 'Kitchen Stations',
                'route' => 'admin.kitchen.stations',
                'icon' => 'grid',
                'icon_type' => 'svg',
                'permission' => 'kitchen.manage',
                'is_route_valid' => $this->validateRoute('admin.kitchen.stations')
            ]
        ];
    }

    /**
     * Get report sub-items
     */
    private function getReportSubItems(): array
    {
        return [
            [
                'title' => 'Sales Reports',
                'route' => 'admin.reports.sales',
                'icon' => 'trending-up',
                'icon_type' => 'svg',
                'permission' => 'reports.view',
                'is_route_valid' => $this->validateRoute('admin.reports.sales')
            ],
            [
                'title' => 'Inventory Reports',
                'route' => 'admin.reports.inventory',
                'icon' => 'package',
                'icon_type' => 'svg',
                'permission' => 'reports.view',
                'is_route_valid' => $this->validateRoute('admin.reports.inventory')
            ]
        ];
    }

    /**
     * Get settings sub-items
     */
    private function getSettingsSubItems(): array
    {
        return [
            [
                'title' => 'General Settings',
                'route' => 'admin.settings.general',
                'icon' => 'cog',
                'icon_type' => 'svg',
                'permission' => 'settings.view',
                'is_route_valid' => $this->validateRoute('admin.settings.general')
            ],
            [
                'title' => 'Payment Settings',
                'route' => 'admin.settings.payments',
                'icon' => 'credit-card',
                'icon_type' => 'svg',
                'permission' => 'settings.payments',
                'is_route_valid' => $this->validateRoute('admin.settings.payments')
            ]
        ];
    }
}
