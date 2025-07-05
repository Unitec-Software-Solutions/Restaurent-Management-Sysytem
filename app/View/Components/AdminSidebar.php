<?php
// filepath: d:\unitec\Restaurent-Management-Sysytem\app\View\Components\AdminSidebar.php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

            // Apply scope restrictions - Super admin sees all
            if (!$this->isSuperAdmin($admin)) {
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
        if (!$admin || !$this->isSuperAdmin($admin)) return 0;

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

            if (!$this->isSuperAdmin($admin) && $admin->organization_id) {
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

            if (!$this->isSuperAdmin($admin)) {
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

            if (!$this->isSuperAdmin($admin)) {
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

            if (!$this->isSuperAdmin($admin)) {
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

            if (!$this->isSuperAdmin($admin)) {
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

            if (!$this->isSuperAdmin($admin) && $admin->organization_id) {
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

            if (!$this->isSuperAdmin($admin)) {
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
     * Production Management Badge Counts
     */
    private function getPendingProductionRequestsCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;

        try {
            $query = \App\Models\ProductionRequestMaster::where('status', \App\Models\ProductionRequestMaster::STATUS_SUBMITTED);

            if (!$this->isSuperAdmin($admin)) {
                $query->where('organization_id', $admin->organization_id);
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getApprovedProductionRequestsCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;

        try {
            $query = \App\Models\ProductionRequestMaster::where('status', \App\Models\ProductionRequestMaster::STATUS_APPROVED);

            if (!$this->isSuperAdmin($admin)) {
                $query->where('organization_id', $admin->organization_id);
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getActiveProductionOrdersCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;

        try {
            $query = \App\Models\ProductionOrder::whereIn('status', ['approved', 'in_progress']);

            if (!$this->isSuperAdmin($admin)) {
                $query->where('organization_id', $admin->organization_id);
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getActiveProductionSessionsCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;

        try {
            $query = \App\Models\ProductionSession::whereIn('status', ['scheduled', 'in_progress']);

            if (!$this->isSuperAdmin($admin)) {
                $query->where('organization_id', $admin->organization_id);
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Centralized Super Admin Check - FIXED
     */
    private function isSuperAdmin($admin): bool
    {
        if (!$admin) return false;
        
        // Primary check: is_super_admin column
        if (isset($admin->is_super_admin) && $admin->is_super_admin) {
            return true;
        }
        
        // Secondary check: isSuperAdmin method if available
        if (method_exists($admin, 'isSuperAdmin')) {
            try {
                return $admin->isSuperAdmin();
            } catch (\Exception $e) {
                Log::warning('isSuperAdmin method failed', ['error' => $e->getMessage()]);
            }
        }
        
        return false;
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
            'icon' => 'fas fa-tachometer-alt',
            'icon_type' => 'fa',
            'permission' => null,
            'badge' => $this->getDashboardNotificationCount(),
            'badge_color' => 'indigo',
            'is_route_valid' => $this->validateRoute('admin.dashboard'),
            'sub_items' => []
        ];

        // Organization Management (Super Admin only)
        if ($this->isSuperAdmin($admin)) {
            $menuItems[] = [
                'title' => 'Organizations',
                'route' => 'admin.organizations.index',
                'route_params' => [],
                'icon' => 'fas fa-building',
                'icon_type' => 'fa',
                'permission' => null, // Super admin doesn't need permission checks
                'badge' => $this->getPendingOrganizationsCount(),
                'badge_color' => 'blue',
                'is_route_valid' => $this->validateRoute('admin.organizations.index'),
                'sub_items' => [
                    [
                        'title' => 'All Organizations',
                        'route' => 'admin.organizations.index',
                        'icon' => 'fas fa-list',
                        'icon_type' => 'fa',
                        'permission' => null,
                        'is_route_valid' => $this->validateRoute('admin.organizations.index')
                    ],
                    [
                        'title' => 'Add Organization',
                        'route' => 'admin.organizations.create',
                        'icon' => 'fas fa-plus',
                        'icon_type' => 'fa',
                        'permission' => null,
                        'is_route_valid' => $this->validateRoute('admin.organizations.create')
                    ],
                    [
                        'title' => 'Activate Organization',
                        'route' => 'admin.organizations.activate.form',
                        'icon' => 'fas fa-key',
                        'icon_type' => 'fa',
                        'permission' => null,
                        'is_route_valid' => $this->validateRoute('admin.organizations.activate.form')
                    ]
                ]
            ];
        }

        // Organization Management (For Organization Admins to manage their own organization)
        if ($admin->organization_id && !$this->isSuperAdmin($admin) && $this->hasPermission($admin, 'organizations.view')) {
            $menuItems[] = [
                'title' => 'Organization Management',
                'route' => 'admin.organization.show',
                'route_params' => ['organization' => $admin->organization_id],
                'icon' => 'fas fa-building',
                'icon_type' => 'fa',
                'permission' => 'organizations.view',
                'badge' => 0,
                'badge_color' => 'blue',
                'is_route_valid' => $this->validateRoute('admin.organization.show', ['organization' => $admin->organization_id]),
                'sub_items' => [
                    [
                        'title' => 'Organization Details',
                        'route' => 'admin.organization.show',
                        'route_params' => ['organization' => $admin->organization_id],
                        'icon' => 'fas fa-eye',
                        'icon_type' => 'fa',
                        'permission' => 'organization.view',
                        'is_route_valid' => $this->validateRoute('admin.organization.show', ['organization' => $admin->organization_id])
                    ],
                    [
                        'title' => 'Edit Organization',
                        'route' => 'admin.organization.edit',
                        'route_params' => ['organization' => $admin->organization_id],
                        'icon' => 'fas fa-pencil-alt',
                        'icon_type' => 'fa',
                        'permission' => 'organization.edit',
                        'is_route_valid' => $this->validateRoute('admin.organization.edit', ['organization' => $admin->organization_id])
                    ],
                    [
                        'title' => 'Organization Settings',
                        'route' => 'admin.organization.settings',
                        'route_params' => ['organization' => $admin->organization_id],
                        'icon' => 'fas fa-cog',
                        'icon_type' => 'fa',
                        'permission' => 'organization.settings',
                        'is_route_valid' => $this->validateRoute('admin.organization.settings', ['organization' => $admin->organization_id])
                    ]
                ]
            ];
        }

        // Branches (Organization/Super Admin)
        if ($admin->organization_id || $this->isSuperAdmin($admin)) {
            $branchRoute = $this->isSuperAdmin($admin) ? 'admin.branches.global' : 'admin.branches.index';
            $branchParams = $this->isSuperAdmin($admin) ? [] : ['organization' => $admin->organization_id];

            $menuItems[] = [
                'title' => 'Branches',
                'route' => $branchRoute,
                'route_params' => $branchParams,
                'icon' => 'fas fa-store',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'branches.view',
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
            'icon' => 'fas fa-shopping-cart',
            'icon_type' => 'fa',
            'permission' => $this->isSuperAdmin($admin) ? null : 'orders.view',
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
                'icon' => 'fas fa-book-open',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'menus.view',
                'badge' => $this->getActiveMenusCount(),
                'badge_color' => 'yellow',
                'is_route_valid' => $this->validateRoute('admin.menus.index'),
                'sub_items' => $this->getMenuSubItems()
            ];
        }

        // Modules Management (Super Admin and Organization Admin)
        if ($this->isSuperAdmin($admin) || ($admin->organization_id && $this->hasPermission($admin, 'modules.view'))) {
            $menuItems[] = [
                'title' => 'Modules',
                'route' => 'admin.modules.index',
                'route_params' => [],
                'icon' => 'fas fa-puzzle-piece',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'modules.view',
                'badge' => $this->getActiveModulesCount(),
                'badge_color' => 'indigo',
                'is_route_valid' => $this->validateRoute('admin.modules.index'),
                'sub_items' => $this->getModulesSubItems()
            ];
        }

        // Subscription Plans Management (Super Admin only)
        if ($this->isSuperAdmin($admin)) {
            $menuItems[] = [
                'title' => 'Subscription Plans',
                'route' => 'admin.subscription-plans.index',
                'route_params' => [],
                'icon' => 'fas fa-credit-card',
                'icon_type' => 'fa',
                'permission' => null, // Super admin doesn't need permission checks
                'badge' => $this->getActiveSubscriptionsCount(),
                'badge_color' => 'green',
                'is_route_valid' => $this->validateRoute('admin.subscription-plans.index'),
                'sub_items' => $this->getSubscriptionPlanSubItems()
            ];
        }

        // Subscription Management (For Organization Admins)
        if (!$this->isSuperAdmin($admin) && $admin->organization_id && $this->hasPermission($admin, 'subscription.view')) {
            $menuItems[] = [
                'title' => 'Subscription',
                'route' => 'admin.subscription.current',
                'route_params' => [],
                'icon' => 'fas fa-file-alt',
                'icon_type' => 'fa',
                'permission' => 'subscription.view',
                'badge' => 0,
                'badge_color' => 'yellow',
                'is_route_valid' => $this->validateRoute('admin.subscription.current'),
                'sub_items' => $this->getSubscriptionManagementSubItems($admin)
            ];
        }

        // Roles & Permissions Management (Super Admin and Organization Admin)
        if ($this->isSuperAdmin($admin) || ($admin->organization_id && $this->hasPermission($admin, 'roles.view'))) {
            $menuItems[] = [
                'title' => 'Roles & Permissions',
                'route' => 'admin.roles.index',
                'route_params' => [],
                'icon' => 'fas fa-shield-alt',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'roles.view',
                'badge' => $this->getActiveRolesCount(),
                'badge_color' => 'emerald',
                'is_route_valid' => $this->validateRoute('admin.roles.index'),
                'sub_items' => $this->getRolesPermissionsSubItems()
            ];
        }

        // Inventory Management
        if ($this->hasPermission($admin, 'inventory.view')) {
            $menuItems[] = [
                'title' => 'Inventory',
                'route' => 'admin.inventory.index',
                'route_params' => [],
                'icon' => 'fas fa-box',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'inventory.view',
                'badge' => $this->getLowStockItemsCount(),
                'badge_color' => 'orange',
                'is_route_valid' => $this->validateRoute('admin.inventory.index'),
                'sub_items' => $this->getInventorySubItems()
            ];
        }

        // Production Management
        if ($this->hasPermission($admin, 'production.view')) {
            $menuItems[] = [
                'title' => 'Production',
                'route' => 'admin.production.index',
                'route_params' => [],
                'icon' => 'fas fa-cogs',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'production.view',
                'badge' => $this->getPendingProductionRequestsCount(),
                'badge_color' => 'blue',
                'is_route_valid' => $this->validateRoute('admin.production.index'),
                'sub_items' => $this->getProductionSubItems()
            ];
        }

        // Suppliers Management (separate from inventory)
        if ($this->hasPermission($admin, 'suppliers.view')) {
            $menuItems[] = [
                'title' => 'Suppliers',
                'route' => 'admin.suppliers.index',
                'route_params' => [],
                'icon' => 'fas fa-truck',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'suppliers.view',
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
                'icon' => 'fas fa-calendar-alt',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'reservations.view',
                'badge' => $this->getTodayReservationsCount(),
                'badge_color' => 'purple',
                'is_route_valid' => $this->validateRoute('admin.reservations.index'),
                'sub_items' => $this->getReservationSubItems()
            ];
        }

        // User Management (Admin level and above)
        if ($this->hasPermission($admin, 'users.view') && !$this->isStaffLevel($admin)) {
            $menuItems[] = [
                'title' => 'User Management',
                'route' => 'admin.users.index',
                'route_params' => [],
                'icon' => 'fas fa-users',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'users.view',
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
                'icon' => 'fas fa-chart-bar',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'reports.view',
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
                'icon' => 'fas fa-hat-chef',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'kitchen.view',
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
                'icon' => 'fas fa-cog',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'settings.view',
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
     * FIXED: Check if admin has specific permission - Super Admin bypass
     */
    private function hasPermission($admin, string $permission): bool
    {
        if (!$admin) {
            return false;
        }

        // CRITICAL FIX: Super admins have ALL permissions - no need to check further
        if ($this->isSuperAdmin($admin)) {
            return true;
        }

        // For regular admins, check specific permissions
        try {
            // First check using Spatie permissions if available
            if (method_exists($admin, 'hasPermissionTo')) {
                return $admin->hasPermissionTo($permission, 'admin');
            }

            // Fallback to basic permission check
            if (method_exists($admin, 'hasPermission')) {
                return $admin->hasPermission($permission);
            }

            // If no permission system is available, allow basic permissions for authenticated admins
            $basicPermissions = [
                'inventory.view', 'inventory.manage', 'suppliers.view', 'suppliers.manage', 
                'production.view', 'production.manage', 'organizations.view', 'branches.view',
                'branches.create', 'branches.activate', 'modules.view', 'roles.view',
                'subscription.view', 'menus.view', 'orders.view', 'reservations.view',
                'users.view', 'reports.view', 'kitchen.view', 'settings.view'
            ];
            
            return in_array($permission, $basicPermissions);

        } catch (\Exception $e) {
            Log::warning('Permission check failed', [
                'permission' => $permission,
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if admin is staff level (lowest access)
     */
    private function isStaffLevel($admin): bool
    {
        if (!$admin) {
            return true;
        }

        // Super admins are never staff level
        if ($this->isSuperAdmin($admin)) {
            return false;
        }

        // Check if admin has management roles
        try {
            if (method_exists($admin, 'hasRole')) {
                return !$admin->hasRole(['Admin', 'Organization Admin', 'Branch Admin', 'Branch Manager'], 'admin');
            }

            // Fallback: if has organization_id, they're likely not staff level
            return !$admin->organization_id;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * FIXED: Check if menu item is accessible by admin
     */
    private function isMenuItemAccessible(array $item, $admin): bool
    {
        // Check route validity
        if (!($item['is_route_valid'] ?? true)) {
            return false;
        }

        // CRITICAL FIX: Super admins can access everything
        if ($this->isSuperAdmin($admin)) {
            return true;
        }

        // Check permission for regular admins
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
            $listRoute = $this->isSuperAdmin($admin) ? 'admin.branches.global' : 'admin.branches.index';
            $listParams = $this->isSuperAdmin($admin) ? [] : ['organization' => $admin->organization_id];

            $subItems[] = [
                'title' => 'All Branches',
                'route' => $listRoute,
                'route_params' => $listParams,
                'icon' => 'fas fa-list',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'branches.view',
                'is_route_valid' => $this->validateRoute($listRoute, $listParams)
            ];
        }

        if ($this->hasPermission($admin, 'branches.create')) {
            $createRoute = 'admin.branches.create';
            $organizationId = $this->isSuperAdmin($admin)
                ? ($admin->organization_id ?? null)
                : $admin->organization_id;

            if ($organizationId || $this->isSuperAdmin($admin)) {
                $createParams = $organizationId ? ['organization' => $organizationId] : [];

                $subItems[] = [
                    'title' => 'Add Branch',
                    'route' => $createRoute,
                    'route_params' => $createParams,
                    'icon' => 'fas fa-plus',
                    'icon_type' => 'fa',
                    'permission' => $this->isSuperAdmin($admin) ? null : 'branches.create',
                    'is_route_valid' => $this->validateRoute($createRoute, $createParams)
                ];
            }
        }

        if ($this->hasPermission($admin, 'branches.activate')) {
            $subItems[] = [
                'title' => 'Activate Branch',
                'route' => 'admin.branches.activate.form',
                'route_params' => [],
                'icon' => 'fas fa-key',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'branches.activate',
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
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'All Orders',
                'route' => 'admin.orders.index',
                'icon' => 'fas fa-list',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'orders.view',
                'is_route_valid' => $this->validateRoute('admin.orders.index')
            ],
            [
                'title' => 'Create Order',
                'route' => 'admin.orders.create',
                'icon' => 'fas fa-plus-circle',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'orders.create',
                'is_route_valid' => $this->validateRoute('admin.orders.create')
            ],
            [
                'title' => 'Dine-In Orders',
                'route' => 'admin.orders.index',
                'route_params' => ['type' => 'in_house'],
                'icon' => 'fas fa-utensils',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'orders.view',
                'is_route_valid' => $this->validateRoute('admin.orders.index')
            ],
            [
                'title' => 'Takeaway Orders',
                'route' => 'admin.orders.index',
                'route_params' => ['type' => 'takeaway'],
                'icon' => 'fas fa-shopping-bag',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'orders.view',
                'is_route_valid' => $this->validateRoute('admin.orders.index')
            ]
        ];
    }

    /**
     * Get menu sub-items - FIXED with correct menu items routes
     */
    private function getMenuSubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'All Menus',
                'route' => 'admin.menus.index',
                'icon' => 'fas fa-list',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'menus.view',
                'is_route_valid' => $this->validateRoute('admin.menus.index')
            ],
            [
                'title' => 'Create Menu',
                'route' => 'admin.menus.create',
                'icon' => 'fas fa-plus',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'menus.create',
                'is_route_valid' => $this->validateRoute('admin.menus.create')
            ],
            [
                'title' => 'Menu Items',
                'route' => 'admin.menu-items.index',
                'icon' => 'fas fa-utensils',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'menus.view',
                'is_route_valid' => $this->validateRoute('admin.menu-items.index')
            ],
            [
                'title' => 'Add Menu Item',
                'route' => 'admin.menu-items.create',
                'icon' => 'fas fa-plus-circle',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'menus.create',
                'is_route_valid' => $this->validateRoute('admin.menu-items.create')
            ],
            [
                'title' => 'Create KOT Items',
                'route' => 'admin.menu-items.create-kot',
                'icon' => 'fas fa-fire',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'menus.create',
                'is_route_valid' => $this->validateRoute('admin.menu-items.create-kot')
            ],
            [
                'title' => 'Menu Categories',
                'route' => 'admin.menu-categories.index',
                'icon' => 'fas fa-tag',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'menus.view',
                'is_route_valid' => $this->validateRoute('admin.menu-categories.index')
            ]
        ];
    }

    /**
     * Get inventory sub-items - FIXED
     */
    private function getInventorySubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'Stock Levels',
                'route' => 'admin.inventory.index',
                'icon' => 'fas fa-box',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'inventory.view',
                'is_route_valid' => $this->validateRoute('admin.inventory.index')
            ],
            [
                'title' => 'Items Management',
                'route' => 'admin.inventory.items.index',
                'icon' => 'fas fa-cube',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'inventory.view',
                'is_route_valid' => $this->validateRoute('admin.inventory.items.index')
            ],
            [
                'title' => 'Suppliers',
                'route' => 'admin.suppliers.index',
                'icon' => 'fas fa-truck',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'suppliers.view',
                'is_route_valid' => $this->validateRoute('admin.suppliers.index')
            ],
            [
                'title' => 'Purchase Orders (GRN)',
                'route' => 'admin.grn.index',
                'icon' => 'fas fa-receipt',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'inventory.view',
                'is_route_valid' => $this->validateRoute('admin.grn.index')
            ]
        ];
    }

    /**
     * Get production sub-items - FIXED
     */
    private function getProductionSubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'Production Dashboard',
                'route' => 'admin.production.index',
                'icon' => 'fas fa-tachometer-alt',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'production.view',
                'is_route_valid' => $this->validateRoute('admin.production.index')
            ],
            [
                'title' => 'Production Requests',
                'route' => 'admin.production.requests.index',
                'icon' => 'fas fa-clipboard-list',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'production.view',
                'is_route_valid' => $this->validateRoute('admin.production.requests.index')
            ],
            [
                'title' => 'Production Orders',
                'route' => 'admin.production.orders.index',
                'icon' => 'fas fa-cog',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'production.view',
                'is_route_valid' => $this->validateRoute('admin.production.orders.index')
            ],
            [
                'title' => 'Production Sessions',
                'route' => 'admin.production.sessions.index',
                'icon' => 'fas fa-play',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'production.view',
                'is_route_valid' => $this->validateRoute('admin.production.sessions.index')
            ],
            [
                'title' => 'Production Recipes',
                'route' => 'admin.production.recipes.index',
                'icon' => 'fas fa-book',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'production.view',
                'is_route_valid' => $this->validateRoute('admin.production.recipes.index')
            ]
        ];
    }

    /**
     * Get supplier sub-items - FIXED
     */
    private function getSupplierSubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'All Suppliers',
                'route' => 'admin.suppliers.index',
                'icon' => 'fas fa-list',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'suppliers.view',
                'is_route_valid' => $this->validateRoute('admin.suppliers.index')
            ],
            [
                'title' => 'Add Supplier',
                'route' => 'admin.suppliers.create',
                'icon' => 'fas fa-plus',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'suppliers.create',
                'is_route_valid' => $this->validateRoute('admin.suppliers.create')
            ]
        ];
    }

    /**
     * Get reservation sub-items - FIXED
     */
    private function getReservationSubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'All Reservations',
                'route' => 'admin.reservations.index',
                'icon' => 'fas fa-list',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'reservations.view',
                'is_route_valid' => $this->validateRoute('admin.reservations.index')
            ],
            [
                'title' => 'Today\'s Reservations',
                'route' => 'admin.reservations.today',
                'icon' => 'fas fa-calendar-day',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'reservations.view',
                'is_route_valid' => $this->validateRoute('admin.reservations.today')
            ]
        ];
    }

    /**
     * Get staff sub-items - FIXED
     */
    private function getStaffSubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'All Staff',
                'route' => 'admin.users.index',
                'icon' => 'fas fa-users',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'users.view',
                'is_route_valid' => $this->validateRoute('admin.users.index')
            ],
            [
                'title' => 'Add Staff',
                'route' => 'admin.users.create',
                'icon' => 'fas fa-user-plus',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'users.create',
                'is_route_valid' => $this->validateRoute('admin.users.create')
            ],
            [
                'title' => 'Roles & Permissions',
                'route' => 'admin.roles.index',
                'icon' => 'fas fa-shield-alt',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'roles.view',
                'is_route_valid' => $this->validateRoute('admin.roles.index')
            ]
        ];
    }

    /**
     * Get kitchen sub-items - FIXED
     */
    private function getKitchenSubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'Active KOTs',
                'route' => 'admin.kitchen.kots',
                'icon' => 'fas fa-receipt',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'kitchen.view',
                'is_route_valid' => $this->validateRoute('admin.kitchen.kots')
            ],
            [
                'title' => 'Kitchen Stations',
                'route' => 'admin.kitchen.stations',
                'icon' => 'fas fa-th',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'kitchen.manage',
                'is_route_valid' => $this->validateRoute('admin.kitchen.stations')
            ]
        ];
    }

    /**
     * Get report sub-items - FIXED
     */
    private function getReportSubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'Sales Reports',
                'route' => 'admin.reports.sales',
                'icon' => 'fas fa-chart-line',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'reports.view',
                'is_route_valid' => $this->validateRoute('admin.reports.sales')
            ],
            [
                'title' => 'Inventory Reports',
                'route' => 'admin.reports.inventory',
                'icon' => 'fas fa-box',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'reports.view',
                'is_route_valid' => $this->validateRoute('admin.reports.inventory')
            ]
        ];
    }

    /**
     * Get settings sub-items - FIXED
     */
    private function getSettingsSubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'General Settings',
                'route' => 'admin.settings.general',
                'icon' => 'fas fa-cog',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'settings.view',
                'is_route_valid' => $this->validateRoute('admin.settings.general')
            ],
            [
                'title' => 'Payment Settings',
                'route' => 'admin.settings.payments',
                'icon' => 'fas fa-credit-card',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'settings.payments',
                'is_route_valid' => $this->validateRoute('admin.settings.payments')
            ]
        ];
    }

    /**
     * Get subscription-related badge counts - FIXED
     */
    private function getActiveSubscriptionsCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || !$this->isSuperAdmin($admin)) return 0;

        try {
            return \App\Models\Organization::whereHas('currentSubscription', function ($q) {
                $q->where('is_active', true)
                  ->where('ends_at', '>=', now());
            })->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getExpiredSubscriptionsCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || !$this->isSuperAdmin($admin)) return 0;

        try {
            return \App\Models\Organization::whereHas('currentSubscription', function ($q) {
                $q->where('is_active', true)
                  ->where('ends_at', '<', now());
            })->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get module access and usage statistics - FIXED
     */
    private function getActiveModulesCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;

        try {
            if ($this->isSuperAdmin($admin)) {
                return \App\Models\Module::where('is_active', true)->count();
            }

            if ($admin->organization_id) {
                $organization = \App\Models\Organization::find($admin->organization_id);
                if ($organization && $organization->subscriptionPlan) {
                    $modules = $organization->subscriptionPlan->getModulesArray();
                    return count($modules);
                }
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get role and permission metrics - FIXED
     */
    private function getActiveRolesCount(): int
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return 0;

        try {
            $query = \Spatie\Permission\Models\Role::where('guard_name', 'admin');

            if (!$this->isSuperAdmin($admin) && $admin->organization_id) {
                $query->where(function ($q) use ($admin) {
                    $q->whereNull('organization_id')
                      ->orWhere('organization_id', $admin->organization_id);
                });
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get subscription plans sub-items (Super Admin only)
     */
    private function getSubscriptionPlanSubItems(): array
    {
        return [
            [
                'title' => 'All Plans',
                'route' => 'admin.subscription-plans.index',
                'icon' => 'fas fa-list',
                'icon_type' => 'fa',
                'permission' => null,
                'is_route_valid' => $this->validateRoute('admin.subscription-plans.index')
            ],
            [
                'title' => 'Create Plan',
                'route' => 'admin.subscription-plans.create',
                'icon' => 'fas fa-plus',
                'icon_type' => 'fa',
                'permission' => null,
                'is_route_valid' => $this->validateRoute('admin.subscription-plans.create')
            ],
            [
                'title' => 'Plan Analytics',
                'route' => 'admin.subscription-plans.analytics',
                'icon' => 'fas fa-chart-bar',
                'icon_type' => 'fa',
                'permission' => null,
                'is_route_valid' => $this->validateRoute('admin.subscription-plans.analytics')
            ]
        ];
    }

    /**
     * Get modules management sub-items - FIXED
     */
    private function getModulesSubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'All Modules',
                'route' => 'admin.modules.index',
                'icon' => 'fas fa-list',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'modules.view',
                'is_route_valid' => $this->validateRoute('admin.modules.index')
            ],
            [
                'title' => 'Add Module',
                'route' => 'admin.modules.create',
                'icon' => 'fas fa-plus',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'modules.create',
                'is_route_valid' => $this->validateRoute('admin.modules.create')
            ],
            [
                'title' => 'Module Configuration',
                'route' => 'admin.modules.config',
                'icon' => 'fas fa-cog',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'modules.configure',
                'is_route_valid' => $this->validateRoute('admin.modules.config')
            ],
            [
                'title' => 'Module Usage Stats',
                'route' => 'admin.modules.stats',
                'icon' => 'fas fa-chart-bar',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'modules.analytics',
                'is_route_valid' => $this->validateRoute('admin.modules.stats')
            ]
        ];
    }

    /**
     * Get roles and permissions sub-items - FIXED
     */
    private function getRolesPermissionsSubItems(): array
    {
        $admin = Auth::guard('admin')->user();
        return [
            [
                'title' => 'All Roles',
                'route' => 'admin.roles.index',
                'icon' => 'fas fa-users',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'roles.view',
                'is_route_valid' => $this->validateRoute('admin.roles.index')
            ],
            [
                'title' => 'Create Role',
                'route' => 'admin.roles.create',
                'icon' => 'fas fa-plus',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'roles.create',
                'is_route_valid' => $this->validateRoute('admin.roles.create')
            ],
            [
                'title' => 'Permissions',
                'route' => 'admin.permissions.index',
                'icon' => 'fas fa-shield-alt',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'permissions.view',
                'is_route_valid' => $this->validateRoute('admin.permissions.index')
            ],
            [
                'title' => 'Role Templates',
                'route' => 'admin.roles.templates',
                'icon' => 'fas fa-file-alt',
                'icon_type' => 'fa',
                'permission' => $this->isSuperAdmin($admin) ? null : 'roles.templates',
                'is_route_valid' => $this->validateRoute('admin.roles.templates')
            ]
        ];
    }

    /**
     * Get subscription management sub-items (for organization admins)
     */
    private function getSubscriptionManagementSubItems($admin): array
    {
        $items = [
            [
                'title' => 'Current Subscription',
                'route' => 'admin.subscription.current',
                'icon' => 'fas fa-eye',
                'icon_type' => 'fa',
                'permission' => 'subscription.view',
                'is_route_valid' => $this->validateRoute('admin.subscription.current')
            ],
            [
                'title' => 'Billing History',
                'route' => 'admin.subscription.billing',
                'icon' => 'fas fa-receipt',
                'icon_type' => 'fa',
                'permission' => 'subscription.billing',
                'is_route_valid' => $this->validateRoute('admin.subscription.billing')
            ]
        ];

        // Add upgrade option if not on highest tier
        if ($admin->organization_id) {
            $items[] = [
                'title' => 'Upgrade Plan',
                'route' => 'admin.subscription.upgrade',
                'icon' => 'fas fa-arrow-up',
                'icon_type' => 'fa',
                'permission' => 'subscription.upgrade',
                'is_route_valid' => $this->validateRoute('admin.subscription.upgrade')
            ];
        }

        return $items;
    }
}
