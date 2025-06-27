<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\{Organization, Module, Admin, User, Order, Reservation, InventoryItem, Employee};

class Sidebar extends Component
{
    public $menuItems;
    public $currentUser;
    public $organization;
    public $branch;
    public $subscription;

    public function __construct()
    {
        try {
            // Null-safe authentication check
            $this->currentUser = $this->getCurrentUserSafe();
            $this->organization = $this->getOrganizationSafe();
            $this->branch = $this->getBranchSafe();
            $this->subscription = $this->getSubscriptionSafe();
            $this->menuItems = $this->buildMenuSafe();
            
            Log::debug('Sidebar initialized successfully', [
                'user_id' => $this->currentUser?->id,
                'user_type' => get_class($this->currentUser ?? new \stdClass()),
                'organization_id' => $this->organization?->id,
                'branch_id' => $this->branch?->id
            ]);
        } catch (\Throwable $e) {
            Log::error('Sidebar initialization failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Initialize safe defaults
            $this->currentUser = null;
            $this->organization = null;
            $this->branch = null;
            $this->subscription = null;
            $this->menuItems = $this->buildFallbackMenu();
        }
    }    /**
     * Safe helper methods for null-safe operations
     */
    private function getCurrentUserSafe()
    {
        try {
            // Try admin guard first, then default
            $user = Auth::guard('admin')->user();
            if (!$user) {
                $user = Auth::user();
            }
            return $user;
        } catch (\Exception $e) {
            Log::warning('Failed to get current user', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function getOrganizationSafe()
    {
        try {
            return $this->currentUser?->organization ?? null;
        } catch (\Exception $e) {
            Log::warning('Failed to get organization', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function getBranchSafe()
    {
        try {
            return $this->currentUser?->branch ?? null;
        } catch (\Exception $e) {
            Log::warning('Failed to get branch', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function getSubscriptionSafe()
    {
        try {
            return $this->organization?->currentSubscription ?? null;
        } catch (\Exception $e) {
            Log::warning('Failed to get subscription', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function buildMenuSafe(): array
    {
        try {
            return $this->buildMenu();
        } catch (\Throwable $e) {
            Log::error('Menu building failed, using fallback', [
                'error' => $e->getMessage(),
                'user_id' => $this->currentUser?->id
            ]);
            return $this->buildFallbackMenu();
        }
    }

    private function buildFallbackMenu(): array
    {
        // Return a basic menu that works without dependencies
        return [
            'main' => [
                [
                    'name' => 'Dashboard',
                    'icon' => 'gauge',
                    'route' => $this->getSafeDashboardRoute(),
                    'permission' => null,
                    'active' => $this->safeRouteCheck('*dashboard*'),
                    'badge' => null
                ]
            ]
        ];
    }

    private function getSafeDashboardRoute(): string
    {
        try {
            if ($this->currentUser) {
                if ($this->isUserSuperAdminSafe()) {
                    return $this->routeExistsSafe('admin.dashboard') ? 'admin.dashboard' : 'dashboard';
                } elseif ($this->isUserAdminSafe()) {
                    return $this->routeExistsSafe('admin.organization.dashboard') ? 'admin.organization.dashboard' : 'admin.dashboard';
                } elseif ($this->isUserBranchAdminSafe()) {
                    return $this->routeExistsSafe('admin.branch.dashboard') ? 'admin.branch.dashboard' : 'admin.dashboard';
                }
            }
            return $this->routeExistsSafe('dashboard') ? 'dashboard' : '#';
        } catch (\Exception $e) {
            Log::warning('Dashboard route detection failed', ['error' => $e->getMessage()]);
            return '#';
        }
    }

    private function routeExistsSafe(string $routeName): bool
    {
        try {
            return Route::has($routeName);
        } catch (\Exception $e) {
            Log::debug('Route check failed', ['route' => $routeName, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function safeRouteCheck(string $pattern): bool
    {
        try {
            return request()->routeIs($pattern);
        } catch (\Exception $e) {
            Log::debug('Route pattern check failed', ['pattern' => $pattern]);
            return false;
        }
    }    /**
     * Build dynamic menu based on user permissions, role, and subscription
     */
    private function buildMenu(): array
    {
        if (!$this->currentUser) {
            Log::debug('No current user found, returning empty menu');
            return [];
        }

        $menu = [];

        try {
            // Dashboard - Always show with role-specific routing
            $menu['main'][] = [
                'name' => 'Dashboard',
                'icon' => 'gauge',
                'route' => $this->getDashboardRoute(),
                'permission' => null,
                'active' => $this->safeRouteCheck('*dashboard*'),
                'badge' => null
            ];

            // Role-specific menu sections with enhanced organization
            if ($this->isUserSuperAdminSafe()) {
                $menu = array_merge_recursive($menu, $this->getSuperAdminMenuSafe());
            } elseif ($this->isUserAdminSafe()) {
                $menu = array_merge_recursive($menu, $this->getOrganizationAdminMenuSafe());
            } elseif ($this->isUserBranchAdminSafe()) {
                $menu = array_merge_recursive($menu, $this->getBranchAdminMenuSafe());        
            } else {
                $menu = array_merge_recursive($menu, $this->getStaffMenuSafe());
            }

            // Module-based menu items (subscription-aware)
            $menu = array_merge_recursive($menu, $this->getModuleBasedMenuSafe());

            // Production requests (new feature)
            if ($this->hasPermissionSafe('view_production') && $this->hasActiveModuleSafe('kitchen')) {
                $menu['production'] = [
                    [
                        'name' => 'Production Requests',
                        'icon' => 'clipboard-list',
                        'route' => 'production.requests.index',
                        'permission' => 'view_production',
                        'active' => $this->safeRouteCheck('production*'),
                        'badge' => $this->getBadgeCountSafe('getPendingProductionRequestsCount')
                    ]
                ];
            }

            Log::debug('Menu built successfully', ['sections' => array_keys($menu)]);
            return $menu;

        } catch (\Throwable $e) {
            Log::error('Menu building failed during processing', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return $this->buildFallbackMenu();
        }
    }/**
     * Get Super Admin specific menu with enhanced organization (SAFE version)
     */
    private function getSuperAdminMenuSafe(): array
    {
        try {
            return $this->getSuperAdminMenu();
        } catch (\Exception $e) {
            Log::error('Super admin menu generation failed', ['error' => $e->getMessage()]);
            return ['main' => []];
        }
    }

    private function getOrganizationAdminMenuSafe(): array
    {
        try {
            return $this->getOrganizationAdminMenu();
        } catch (\Exception $e) {
            Log::error('Org admin menu generation failed', ['error' => $e->getMessage()]);
            return ['main' => []];
        }
    }

    private function getBranchAdminMenuSafe(): array
    {
        try {
            return $this->getBranchAdminMenu();
        } catch (\Exception $e) {
            Log::error('Branch admin menu generation failed', ['error' => $e->getMessage()]);
            return ['main' => []];
        }
    }

    private function getStaffMenuSafe(): array
    {
        try {
            return $this->getStaffMenu();
        } catch (\Exception $e) {
            Log::error('Staff menu generation failed', ['error' => $e->getMessage()]);
            return ['main' => []];
        }
    }

    private function getModuleBasedMenuSafe(): array
    {
        try {
            return $this->getModuleBasedMenu();
        } catch (\Exception $e) {
            Log::error('Module-based menu generation failed', ['error' => $e->getMessage()]);
            return ['modules' => []];
        }
    }

    private function hasActiveModuleSafe(string $module): bool
    {
        try {
            return $this->hasActiveModule($module);
        } catch (\Exception $e) {
            Log::error("Active module check failed for: $module", ['error' => $e->getMessage()]);
            return false;
        }
    }
    private function getSuperAdminMenu(): array
    {
        return [
            'main' => [
                [
                    'name' => 'Organizations',
                    'icon' => 'building',
                    'route' => 'admin.organizations.index',
                    'permission' => 'view_organizations',
                    'active' => $this->safeRouteCheck('admin.organizations*'),
                    'sub_items' => [
                        [
                            'name' => 'All Organizations',
                            'route' => 'admin.organizations.index',
                            'icon' => 'list',
                            'permission' => 'view_organizations'
                        ],
                        [
                            'name' => 'Create Organization',
                            'route' => 'admin.organizations.create',
                            'icon' => 'plus',
                            'permission' => 'create_organizations'
                        ],
                        [
                            'name' => 'Activate Organization',
                            'route' => 'admin.organizations.activate.form',
                            'icon' => 'key',
                            'permission' => 'manage_organizations'
                        ]
                    ]
                ],                [
                    'name' => 'Branches',
                    'icon' => 'store',
                    'route' => 'admin.branches.global',
                    'permission' => 'view_branches',
                    'active' => $this->safeRouteCheck('admin.branches*'),
                    'sub_items' => [
                        [
                            'name' => 'All Branches',
                            'route' => 'admin.branches.global',
                            'icon' => 'list',
                            'permission' => 'view_branches'
                        ],
                        [
                            'name' => 'Activate Branch',
                            'route' => 'admin.branches.activate.form',
                            'icon' => 'key',
                            'permission' => 'activate_branches'
                        ]
                    ]
                ]
            ],
            'subscription' => [
                [
                    'name' => 'Subscription Plans',
                    'icon' => 'credit-card',
                    'route' => 'admin.subscription-plans.index',
                    'permission' => 'manage_subscriptions',
                    'active' => $this->safeRouteCheck('admin.subscription-plans*')
                ],
                [
                    'name' => 'Subscription Management',
                    'icon' => 'briefcase',
                    'route' => 'admin.subscriptions.index',
                    'permission' => 'manage_subscriptions',                    'active' => $this->safeRouteCheck('admin.subscriptions*'),
                    'badge' => $this->getBadgeCountSafe('getExpiredSubscriptionsCount')
                ]
            ],
            'admin' => [
                [
                    'name' => 'Roles & Permissions',
                    'icon' => 'lock',
                    'route' => 'admin.roles.index',
                    'permission' => 'manage_roles',
                    'active' => $this->safeRouteCheck('admin.roles*')
                ],
                [
                    'name' => 'Modules Management',
                    'icon' => 'puzzle',
                    'route' => 'admin.modules.index',
                    'permission' => 'manage_modules',
                    'active' => $this->safeRouteCheck('admin.modules*')
                ],
                [
                    'name' => 'System Settings',
                    'icon' => 'cogs',
                    'route' => 'admin.settings.index',
                    'permission' => 'manage_settings',
                    'active' => $this->safeRouteCheck('admin.settings*')
                ]
            ]
        ];
    }    /**
     * Get Organization Admin specific menu with subscription awareness
     */
    private function getOrganizationAdminMenu(): array
    {
        return [
            'main' => [
                [
                    'name' => 'Branches',
                    'icon' => 'store',
                    'route' => 'admin.branches.index',                    'route_params' => ['organization' => $this->organization?->id],
                    'permission' => 'view_branches',
                    'active' => $this->safeRouteCheck('admin.branches*'),
                    'sub_items' => [
                        [
                            'name' => 'All Branches',
                            'route' => 'admin.branches.index',
                            'route_params' => ['organization' => $this->organization?->id],
                            'icon' => 'list',
                            'permission' => 'view_branches'
                        ],
                        [
                            'name' => 'Create Branch',
                            'route' => 'admin.branches.create',
                            'route_params' => ['organization' => $this->organization?->id],
                            'icon' => 'plus',
                            'permission' => 'create_branches'
                        ]
                    ]
                ],
                [
                    'name' => 'Users',
                    'icon' => 'users',
                    'route' => 'admin.users.index',                    'permission' => 'view_users',
                    'active' => $this->safeRouteCheck('admin.users*'),
                    'badge' => $this->getBadgeCountSafe('getPendingUsersCount')
                ]
            ],
            'subscription' => [                [
                    'name' => 'Subscription Overview',
                    'icon' => 'chart-bar',
                    'route' => 'admin.subscription.overview',
                    'permission' => 'view_subscription',
                    'active' => $this->safeRouteCheck('admin.subscription*'),
                    'badge' => $this->subscription && $this->safeMethodCall($this->subscription, 'isExpired') ? '!' : null
                ],                [
                    'name' => 'Organization Settings',
                    'icon' => 'settings',
                    'route' => 'admin.organizations.edit',
                    'route_params' => [$this->organization?->id],
                    'permission' => 'edit_organizations',
                    'active' => $this->safeRouteCheck('admin.organizations.edit*')
                ]
            ]
        ];
    }

    /**
     * Get Branch Admin specific menu with operational focus
     */
    private function getBranchAdminMenu(): array
    {
        return [
            'main' => [                [
                    'name' => 'Staff Management',
                    'icon' => 'users',
                    'route' => 'admin.staff.index',
                    'permission' => 'manage_staff',
                    'active' => $this->safeRouteCheck('admin.staff*'),
                    'badge' => $this->getBadgeCountSafe('getPendingStaffCount')
                ],                [
                    'name' => 'Schedules',
                    'icon' => 'calendar',
                    'route' => 'admin.schedules.index',
                    'permission' => 'manage_schedules',
                    'active' => $this->safeRouteCheck('admin.schedules*')
                ]
            ],
            'operations' => [                [
                    'name' => 'Daily Operations',
                    'icon' => 'clipboard-check',
                    'route' => 'admin.operations.daily',
                    'permission' => 'view_operations',
                    'active' => $this->safeRouteCheck('admin.operations*')
                ]
            ]
        ];
    }

    /**
     * Get Staff specific menu based on role with real-time indicators
     */
    private function getStaffMenu(): array
    {
        $menu = ['main' => []];        // Kitchen staff menu with enhanced features
        if ($this->hasAnyRoleSafe(['chef', 'kitchen-manager', 'kitchen-staff', 'chefs', 'kitchen-managers'])) {
            $menu['kitchen'] = [                [
                    'name' => 'Kitchen Orders (KOT)',
                    'icon' => 'chef-hat',
                    'route' => 'kitchen.orders.index',
                    'permission' => 'view-kitchen-orders',
                    'active' => $this->safeRouteCheck('kitchen.orders*'),
                    'badge' => $this->getBadgeCountSafe('getPendingKitchenOrdersCount')
                ],                [
                    'name' => 'Kitchen Stations',
                    'icon' => 'industry',
                    'route' => 'kitchen.stations.index',
                    'permission' => 'manage-kitchen-stations',
                    'active' => $this->safeRouteCheck('kitchen.stations*'),
                    'sub_items' => [
                        [
                            'name' => 'Hot Kitchen',
                            'route' => 'kitchen.stations.show',
                            'route_params' => ['station' => 'hot'],
                            'icon' => 'flame',
                            'permission' => 'view-kitchen-stations'
                        ],
                        [
                            'name' => 'Cold Kitchen',
                            'route' => 'kitchen.stations.show',
                            'route_params' => ['station' => 'cold'],
                            'icon' => 'snowflake',
                            'permission' => 'view-kitchen-stations'
                        ],
                        [
                            'name' => 'Grill Station',
                            'route' => 'kitchen.stations.show',
                            'route_params' => ['station' => 'grill'],
                            'icon' => 'flame',
                            'permission' => 'view-kitchen-stations'
                        ]
                    ]
                ]
            ];            if ($this->hasRoleSafe('kitchen-manager') || $this->hasRoleSafe('kitchen-managers')) {
                $menu['kitchen'][] = [
                    'name' => 'Production Planning',
                    'icon' => 'clipboard-list',
                    'route' => 'kitchen.production.index',
                    'permission' => 'manage-kitchen-staff',
                    'active' => $this->safeRouteCheck('kitchen.production*')
                ];
            }
        }        // Service staff menu with enhanced functionality
        if ($this->hasAnyRoleSafe(['server', 'host/hostess', 'cashier', 'servers', 'cashiers'])) {
            $menu['service'] = [
                [
                    'name' => 'Tables & Reservations',
                    'icon' => 'calendar-clock',
                    'route' => 'reservations.index',
                    'permission' => 'manage-reservations',                    'active' => $this->safeRouteCheck('reservations*'),
                    'badge' => $this->getBadgeCountSafe('getPendingReservationsCount')
                ]
            ];

            if ($this->hasRoleSafe('server') || $this->hasRoleSafe('servers')) {
                $menu['service'][] =                [
                    'name' => 'My Orders',
                    'icon' => 'shopping-cart',
                    'route' => 'orders.my-orders',
                    'permission' => 'take-orders',
                    'active' => $this->safeRouteCheck('orders.my-orders*'),
                    'badge' => $this->safeMethodCall($this, 'getMyActiveOrdersCount')
                ];
            }            if ($this->hasRoleSafe('cashier') || $this->hasRoleSafe('cashiers')) {
                $menu['service'][] = [
                    'name' => 'Payment Processing',
                    'icon' => 'credit-card',
                    'route' => 'payments.index',
                    'permission' => 'process-payments',
                    'active' => $this->safeRouteCheck('payments*'),
                    'badge' => $this->getBadgeCountSafe('getPendingPaymentsCount')
                ];
            }
        }        // Inventory staff menu with alerts
        if ($this->hasAnyRoleSafe(['inventory-manager', 'chef', 'chefs'])) {
            $menu['inventory'] = [
                [
                    'name' => 'Inventory Items',
                    'icon' => 'package',                    'route' => 'inventory.items.index',
                    'permission' => 'view_inventory',
                    'active' => $this->safeRouteCheck('inventory.items*'),
                    'badge' => $this->safeMethodCall($this, 'getLowStockCount')
                ],                [
                    'name' => 'Stock Adjustments',
                    'icon' => 'adjust',
                    'route' => 'inventory.adjustments.index',
                    'permission' => 'adjust_inventory',
                    'active' => $this->safeRouteCheck('inventory.adjustments*')
                ],                [
                    'name' => 'Stock Alerts',
                    'icon' => 'alert-triangle',
                    'route' => 'inventory.alerts.index',
                    'permission' => 'view_inventory',
                    'active' => $this->safeRouteCheck('inventory.alerts*'),
                    'badge' => $this->safeMethodCall($this, 'getOutOfStockCount')
                ]
            ];
        }

        return $menu;
    }

    /**
     * Get module-based menu items with subscription awareness
     */
    private function getModuleBasedMenu(): array
    {
        $menu = ['modules' => []];

        if (!$this->organization || !$this->subscription) {
            return $menu;
        }

        // Get active modules from subscription
        $activeModules = $this->getActiveModules();

        foreach ($activeModules as $moduleConfig) {
            $moduleName = is_array($moduleConfig) ? $moduleConfig['name'] : $moduleConfig;
            $moduleTier = is_array($moduleConfig) ? ($moduleConfig['tier'] ?? 'basic') : 'basic';
            
            switch ($moduleName) {                case 'pos':
                    if ($this->hasPermissionSafe('pos.access')) {
                        $menu['modules'][] = [
                            'name' => 'Point of Sale',
                            'icon' => 'calculator',
                            'route' => 'pos.index',
                            'permission' => 'pos.access',
                            'active' => $this->safeRouteCheck('pos*'),
                            'tier' => $moduleTier
                        ];
                    }
                    break;                case 'inventory':
                    if ($this->hasPermissionSafe('view_inventory')) {
                        $inventoryMenu = [
                            'name' => 'Inventory',
                            'icon' => 'package',
                            'route' => 'admin.inventory.index',
                            'permission' => 'view_inventory',
                            'active' => $this->safeRouteCheck('admin.inventory*'),
                            'badge' => $this->safeMethodCall($this, 'getLowStockCount'),
                            'tier' => $moduleTier,
                            'sub_items' => [
                                [
                                    'name' => 'Items',
                                    'route' => 'admin.inventory.items.index',
                                    'icon' => 'box',
                                    'permission' => 'view_inventory'
                                ],
                                [
                                    'name' => 'Stock Management',
                                    'route' => 'admin.inventory.stock.index',
                                    'icon' => 'trending-up',
                                    'permission' => 'manage_inventory'
                                ]
                            ]
                        ];

                        // Add premium features for higher tiers
                        if ($moduleTier === 'premium' || $moduleTier === 'enterprise') {
                            $inventoryMenu['sub_items'][] = [
                                'name' => 'Suppliers',
                                'route' => 'admin.suppliers.index',
                                'icon' => 'truck',
                                'permission' => 'manage_suppliers'
                            ];
                            $inventoryMenu['sub_items'][] = [
                                'name' => 'GRN',
                                'route' => 'admin.grn.index',
                                'icon' => 'clipboard-check',
                                'permission' => 'manage_grn'
                            ];
                        }

                        if ($moduleTier === 'enterprise') {
                            $inventoryMenu['sub_items'][] = [
                                'name' => 'Analytics',
                                'route' => 'admin.inventory.analytics.index',
                                'icon' => 'chart-bar',
                                'permission' => 'view_inventory_analytics'
                            ];
                        }

                        $menu['modules'][] = $inventoryMenu;
                    }
                    break;                case 'reservations':
                    if ($this->hasPermissionSafe('view_reservations')) {
                        $menu['modules'][] = [
                            'name' => 'Reservations',
                            'icon' => 'calendar-clock',
                            'route' => 'admin.reservations.index',
                            'permission' => 'view_reservations',
                            'active' => $this->safeRouteCheck('admin.reservations*'),
                            'badge' => $this->safeMethodCall($this, 'getPendingReservationsCount'),
                            'tier' => $moduleTier
                        ];
                    }
                    break;                case 'orders':
                    if ($this->hasPermissionSafe('view_orders')) {
                        $menu['modules'][] = [
                            'name' => 'Orders',
                            'icon' => 'shopping-cart',
                            'route' => 'admin.orders.index',
                            'permission' => 'view_orders',
                            'active' => $this->safeRouteCheck('admin.orders*'),
                            'badge' => $this->safeMethodCall($this, 'getPendingOrdersCount'),
                            'tier' => $moduleTier
                        ];
                    }
                    break;                case 'kitchen':
                    if ($this->hasPermissionSafe('view-kitchen-orders')) {
                        $kitchenMenu = [
                            'name' => 'Kitchen Management',
                            'icon' => 'chef-hat',
                            'route' => 'admin.kitchen.index',
                            'permission' => 'view-kitchen-orders',
                            'active' => $this->safeRouteCheck('admin.kitchen*'),
                            'badge' => $this->safeMethodCall($this, 'getPendingKOTCount'),
                            'tier' => $moduleTier,
                            'sub_items' => [
                                [
                                    'name' => 'KOT Dashboard',
                                    'route' => 'admin.kitchen.kot.index',
                                    'icon' => 'monitor',
                                    'permission' => 'view-kitchen-orders'
                                ],
                                [
                                    'name' => 'Order Queue',
                                    'route' => 'admin.kitchen.queue.index',
                                    'icon' => 'list',
                                    'permission' => 'view-kitchen-orders'
                                ]
                            ]
                        ];

                        if ($moduleTier === 'premium' || $moduleTier === 'enterprise') {
                            $kitchenMenu['sub_items'][] = [
                                'name' => 'Production Planning',
                                'route' => 'admin.kitchen.production.index',
                                'icon' => 'calendar-check',
                                'permission' => 'manage-kitchen-production'
                            ];
                        }

                        $menu['modules'][] = $kitchenMenu;
                    }
                    break;                case 'staff':
                    if ($this->hasPermissionSafe('view_staff') && ($moduleTier === 'premium' || $moduleTier === 'enterprise')) {
                        $menu['modules'][] = [
                            'name' => 'Staff Management',
                            'icon' => 'user-group',
                            'route' => 'admin.staff.index',
                            'permission' => 'view_staff',
                            'active' => $this->safeRouteCheck('admin.staff*'),
                            'tier' => $moduleTier
                        ];
                    }
                    break;                case 'reports':
                    if ($this->hasPermissionSafe('view_reports')) {
                        $reportsMenu = [
                            'name' => 'Reports',
                            'icon' => 'bar-chart-3',
                            'route' => 'admin.reports.index',
                            'permission' => 'view_reports',
                            'active' => $this->safeRouteCheck('admin.reports*'),
                            'tier' => $moduleTier
                        ];

                        if ($moduleTier === 'enterprise') {
                            $reportsMenu['sub_items'] = [
                                [
                                    'name' => 'Advanced Analytics',
                                    'route' => 'admin.reports.analytics.index',
                                    'icon' => 'trending-up',
                                    'permission' => 'view_advanced_reports'
                                ]
                            ];
                        }                        $menu['modules'][] = $reportsMenu;
                    }
                    break;
                case 'analytics':
                    if ($this->hasPermissionSafe('view_analytics') && $moduleTier === 'enterprise') {
                        $menu['modules'][] = [
                            'name' => 'Advanced Analytics',
                            'icon' => 'chart-pie',
                            'route' => 'admin.analytics.index',
                            'permission' => 'view_analytics',
                            'active' => $this->safeRouteCheck('admin.analytics*'),
                            'tier' => $moduleTier
                        ];
                    }
                    break;
            }
        }

        return $menu;
    }    /**
     * Helper methods for checking user roles with fallback compatibility (SAFE versions)
     */
    private function isUserSuperAdminSafe(): bool
    {
        try {
            if (!$this->currentUser) return false;
            
            if ($this->currentUser instanceof Admin) {
                return $this->safeMethodCall($this->currentUser, 'isSuperAdmin') ?? false;
            }
            
            if ($this->currentUser instanceof User) {
                return $this->currentUser->is_super_admin ?? false;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::debug('Super admin check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function isUserAdminSafe(): bool
    {
        try {
            if (!$this->currentUser) return false;
            
            if ($this->currentUser instanceof Admin) {
                return $this->safeMethodCall($this->currentUser, 'isAdmin') ?? false;
            }
            
            if ($this->currentUser instanceof User) {
                return $this->currentUser->is_admin ?? false;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::debug('Admin check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function isUserBranchAdminSafe(): bool
    {
        try {
            if (!$this->currentUser) return false;
            
            if ($this->currentUser instanceof Admin) {
                return $this->safeMethodCall($this->currentUser, 'isBranchAdmin') ?? false;
            }
            
            if ($this->currentUser instanceof User) {
                return $this->hasRoleSafe('branch_admin');
            }
            
            return false;
        } catch (\Exception $e) {
            Log::debug('Branch admin check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function safeMethodCall($object, string $method, array $params = [])
    {
        try {
            if (!$object || !method_exists($object, $method)) {
                Log::debug("Method does not exist", ['method' => $method, 'class' => get_class($object ?? new \stdClass())]);
                return null;
            }
            return call_user_func_array([$object, $method], $params);
        } catch (\Exception $e) {
            Log::warning("Safe method call failed", ['method' => $method, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Helper methods for checking user roles with fallback compatibility (ORIGINAL - now refactored for safety)
     */
    private function isUserSuperAdmin(): bool
    {
        return $this->isUserSuperAdminSafe();
    }

    private function isUserAdmin(): bool
    {
        return $this->isUserAdminSafe();
    }

    private function isUserBranchAdmin(): bool
    {
        return $this->isUserBranchAdminSafe();
    }    /**
     * Helper methods for permissions and roles with enhanced compatibility and safety
     */
    private function hasPermissionSafe(string $permission): bool
    {
        try {
            if (!$this->currentUser) return false;
            
            // Handle Admin model
            if ($this->currentUser instanceof Admin) {
                if ($this->safeMethodCall($this->currentUser, 'can', [$permission])) {
                    return true;
                }
                return $this->isUserSuperAdminSafe();
            }
            
            // Handle User model
            if ($this->currentUser instanceof User) {
                if ($this->safeMethodCall($this->currentUser, 'can', [$permission])) {
                    return true;
                }
                return $this->currentUser->is_super_admin ?? false;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("Permission check failed for: $permission", ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function hasRoleSafe(string $role): bool
    {
        try {
            if (!$this->currentUser) return false;
            
            if ($this->safeMethodCall($this->currentUser, 'hasRole', [$role])) {
                return true;
            }
            
            // Fallback for User model without Spatie
            if ($this->currentUser instanceof User && isset($this->currentUser->role)) {
                return $this->currentUser->role === $role;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("Role check failed for: $role", ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function hasAnyRoleSafe(array $roles): bool
    {
        try {
            if (!$this->currentUser) return false;
            
            if ($this->safeMethodCall($this->currentUser, 'hasAnyRole', [$roles])) {
                return true;
            }
            
            // Fallback check
            foreach ($roles as $role) {
                if ($this->hasRoleSafe($role)) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("Any role check failed", ['roles' => $roles, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function hasPermission(string $permission): bool
    {
        return $this->hasPermissionSafe($permission);
    }

    private function hasRole(string $role): bool
    {
        return $this->hasRoleSafe($role);
    }

    private function hasAnyRole(array $roles): bool
    {
        return $this->hasAnyRoleSafe($roles);
    }    /**
     * Check if organization has active module
     */
    private function hasActiveModule(string $module): bool
    {
        try {
            if (!$this->organization || !$this->subscription) {
                return false;
            }

            return $this->safeMethodCall($this->organization, 'hasModule', [$module]) ?? false;
        } catch (\Exception $e) {
            Log::error("Active module check failed for: $module", ['error' => $e->getMessage()]);
            return false;
        }
    }/**
     * Get dashboard route based on user role (SAFE version)
     */
    private function getDashboardRoute(): string
    {
        try {
            if (!$this->currentUser) return $this->getSafeDashboardRoute();

            if ($this->isUserSuperAdminSafe()) {
                return $this->routeExistsSafe('admin.dashboard') ? 'admin.dashboard' : '#';
            } elseif ($this->isUserAdminSafe()) {
                return $this->routeExistsSafe('admin.organization.dashboard') ? 'admin.organization.dashboard' : 'admin.dashboard';
            } elseif ($this->isUserBranchAdminSafe()) {
                return $this->routeExistsSafe('admin.branch.dashboard') ? 'admin.branch.dashboard' : 'admin.dashboard';
            } else {
                // Role-specific dashboards
                if ($this->hasRoleSafe('chef') || $this->hasRoleSafe('kitchen-manager')) {
                    return $this->routeExistsSafe('kitchen.dashboard') ? 'kitchen.dashboard' : 'dashboard';
                } elseif ($this->hasRoleSafe('server') || $this->hasRoleSafe('host/hostess')) {
                    return $this->routeExistsSafe('service.dashboard') ? 'service.dashboard' : 'dashboard';
                } elseif ($this->hasRoleSafe('cashier')) {
                    return $this->routeExistsSafe('pos.dashboard') ? 'pos.dashboard' : 'dashboard';
                }
                return $this->routeExistsSafe('dashboard') ? 'dashboard' : '#';
            }
        } catch (\Exception $e) {
            Log::warning('Dashboard route detection failed', ['error' => $e->getMessage()]);
            return $this->getSafeDashboardRoute();
        }
    }

    /**
     * Get active modules from subscription with tier information
     */
    private function getActiveModules(): array
    {
        if (!$this->organization || !$this->subscription) {
            return [];
        }

        $plan = $this->subscription->plan;
        
        if ($plan && $plan->modules) {
            $modules = is_array($plan->modules) ? $plan->modules : json_decode($plan->modules, true);
            return $modules ?? [];
        }

        // Default modules for backward compatibility
        return [
            ['name' => 'pos', 'tier' => 'basic'],
            ['name' => 'kitchen', 'tier' => 'basic'],
            ['name' => 'reservations', 'tier' => 'basic'],
            ['name' => 'orders', 'tier' => 'basic']
        ];
    }    /**
     * Badge count methods with real-time data (SAFE implementations)
     */
    private function getBadgeCountSafe(string $method): ?int
    {
        try {
            if (!$this->currentUser) return 0;
            return $this->$method();
        } catch (\Exception $e) {
            Log::error("Badge count failed for: $method", ['error' => $e->getMessage()]);
            return 0;
        }
    }    private function getPendingUsersCount(): ?int
    {
        if (!$this->hasPermissionSafe('view_users')) return null;
        
        try {
            if (!$this->organization) return 0;
            return $this->safeMethodCall($this->organization, 'users') 
                ? $this->organization->users()->where('is_active', false)->count()
                : 0;
        } catch (\Exception $e) {
            Log::error('Pending users count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }    private function getPendingStaffCount(): ?int
    {
        if (!$this->hasPermissionSafe('manage_staff')) return null;
        
        try {
            if (!$this->branch) return 0;
            return $this->safeMethodCall($this->branch, 'users')
                ? $this->branch->users()->where('is_active', false)->count()
                : 0;
        } catch (\Exception $e) {
            Log::error('Pending staff count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getPendingKitchenOrdersCount(): ?int
    {
        if (!$this->hasPermissionSafe('view-kitchen-orders')) return null;
        
        try {
            // Use DB table query instead of model to avoid dependency issues
            return DB::table('kitchen_orders')
                ->where('branch_id', $this->branch?->id)
                ->whereIn('status', ['pending', 'preparing'])
                ->count();
        } catch (\Exception $e) {
            Log::error('Kitchen orders count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getPendingKOTCount(): ?int
    {
        return $this->getPendingKitchenOrdersCount();
    }

    private function getPendingReservationsCount(): ?int
    {
        if (!$this->hasPermissionSafe('view_reservations')) return null;
        
        try {
            return Reservation::where('branch_id', $this->branch?->id)
                ->where('status', 'pending')
                ->whereDate('reservation_date', '>=', today())
                ->count();
        } catch (\Exception $e) {
            Log::error('Pending reservations count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getPendingOrdersCount(): ?int
    {
        if (!$this->hasPermissionSafe('view_orders')) return null;
        
        try {
            return Order::where('branch_id', $this->branch?->id)
                ->whereIn('status', ['pending', 'confirmed', 'preparing'])
                ->count();
        } catch (\Exception $e) {
            Log::error('Pending orders count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getMyActiveOrdersCount(): ?int
    {
        if (!$this->hasPermissionSafe('take-orders')) return null;
        
        try {
            return Order::where('server_id', $this->currentUser?->id)
                ->whereIn('status', ['pending', 'confirmed', 'preparing'])
                ->count();
        } catch (\Exception $e) {
            Log::error('My active orders count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getPendingPaymentsCount(): ?int
    {
        if (!$this->hasPermissionSafe('process-payments')) return null;
        
        try {
            return Order::where('branch_id', $this->branch?->id)
                ->where('payment_status', 'pending')
                ->count();
        } catch (\Exception $e) {
            Log::error('Pending payments count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getLowStockCount(): ?int
    {
        if (!$this->hasPermissionSafe('view_inventory')) return null;
        
        try {
            return InventoryItem::where('organization_id', $this->organization?->id)
                ->whereRaw('quantity <= minimum_quantity')
                ->count();
        } catch (\Exception $e) {
            Log::error('Low stock count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getOutOfStockCount(): ?int
    {
        if (!$this->hasPermissionSafe('view_inventory')) return null;
        
        try {
            return InventoryItem::where('organization_id', $this->organization?->id)
                ->where('quantity', 0)
                ->count();
        } catch (\Exception $e) {
            Log::error('Out of stock count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getPendingProductionRequestsCount(): ?int
    {
        if (!$this->hasPermissionSafe('view_production')) return null;
        
        try {
            return DB::table('production_requests')
                ->where('branch_id', $this->branch?->id)
                ->where('status', 'pending')
                ->count();
        } catch (\Exception $e) {
            Log::error('Production requests count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function getExpiredSubscriptionsCount(): ?int
    {
        if (!$this->hasPermissionSafe('manage_subscriptions')) return null;
        
        try {
            return DB::table('subscriptions')
                ->where('end_date', '<', now())
                ->where('is_active', true)
                ->count();
        } catch (\Exception $e) {
            Log::error('Expired subscriptions count failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }    public function render()
    {
        try {
            // Check if user is authenticated using safe method
            if (!$this->currentUser) {
                Log::debug('No authenticated user, rendering basic sidebar');
                return view('components.sidebar', [
                    'menuItems' => $this->buildFallbackMenu(),
                    'currentUser' => null,
                    'organization' => null,
                    'branch' => null,
                    'subscription' => null
                ]);
            }

            // Render with menu items
            Log::debug('Rendering sidebar with menu', [
                'user_id' => $this->currentUser?->id,
                'sections' => array_keys($this->menuItems)
            ]);

            return view('components.sidebar', [
                'menuItems' => $this->menuItems,
                'currentUser' => $this->currentUser,
                'organization' => $this->organization,
                'branch' => $this->branch,
                'subscription' => $this->subscription
            ]);

        } catch (\Throwable $e) {
            Log::critical('Sidebar render failed completely', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return the view with fallback menu
            try {
                return view('components.sidebar', [
                    'menuItems' => ['main' => [
                        [
                            'name' => 'Dashboard',
                            'icon' => 'gauge',
                            'route' => '#',
                            'permission' => null,
                            'active' => false,
                            'badge' => null
                        ]
                    ]],
                    'currentUser' => null,
                    'organization' => null,
                    'branch' => null,
                    'subscription' => null
                ]);
            } catch (\Exception $viewError) {
                Log::emergency('Even fallback sidebar render failed', ['error' => $viewError->getMessage()]);
                // Last resort: return empty string
                return '';
            }
        }
    }
}