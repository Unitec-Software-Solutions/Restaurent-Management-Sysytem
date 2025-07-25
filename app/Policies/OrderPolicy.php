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
        return $admin->hasPermissionTo('orders.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Order $order): bool
    {
        if (!$admin->hasPermissionTo('orders.view')) return false;
        // Only allow if same organization
        return data_get($admin, 'organization_id') === data_get($order, 'organization_id');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('orders.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Order $order): bool
    {
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return false;
        }
        if (!$admin->hasPermissionTo('orders.edit')) return false;
        return data_get($admin, 'organization_id') === data_get($order, 'organization_id');
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(Admin $admin, Order $order): bool
    {
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return false;
        }
        if (!$admin->hasPermissionTo('orders.cancel')) return false;
        return data_get($admin, 'organization_id') === data_get($order, 'organization_id');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Order $order): bool
    {
        // Deletion of orders is forbidden for all users to maintain data integrity
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Order $order): bool
    {
        // Restoration of orders is forbidden for all users
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Order $order): bool
    {
        // Permanent deletion of orders is forbidden for all users
        return false;
    }
}
