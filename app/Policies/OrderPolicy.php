<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Auth\Access\Response;
use App\Services\PermissionSystemService;

class OrderPolicy
{
    protected PermissionSystemService $permissionService;

    public function __construct(PermissionSystemService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['orders.view']) && $admin->hasPermissionTo('view orders');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Order $order): bool
    {
        if ($admin->is_super_admin) {
            return true;
        }

        // Admin can view orders from their branch/organization
        return $admin->branch_id === $order->branch_id || 
               $admin->organization_id === $order->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['orders.create']) && $admin->hasPermissionTo('create orders');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Order $order): bool
    {
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return false;
        }
        if ($admin->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        if (!isset($availablePermissions['orders.edit']) || !$admin->hasPermissionTo('update orders')) {
            return false;
        }
        return $admin->branch_id === $order->branch_id || $admin->organization_id === $order->organization_id;
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(Admin $admin, Order $order): bool
    {
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return false;
        }
        if ($admin->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        if (!isset($availablePermissions['orders.cancel']) || !$admin->hasPermissionTo('cancel orders')) {
            return false;
        }
        return $admin->branch_id === $order->branch_id || $admin->organization_id === $order->organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Order $order): bool
    {
        if ($order->status !== Order::STATUS_PENDING) {
            return false;
        }
        if ($admin->is_super_admin) return true;
        $permissionDefinitions = $this->permissionService->getPermissionDefinitions();
        $modulesConfig = config('modules');
        $availablePermissions = $this->permissionService->filterPermissionsBySubscription($admin, $permissionDefinitions, $modulesConfig);
        return isset($availablePermissions['orders.delete']) && $admin->hasPermissionTo('delete orders') &&
            ($admin->branch_id === $order->branch_id || $admin->organization_id === $order->organization_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Order $order): bool
    {
        return false;
    }
}
