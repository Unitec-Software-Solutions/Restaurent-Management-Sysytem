<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('view orders') || $admin->is_super_admin;
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
        return $admin->hasPermissionTo('create orders') || $admin->is_super_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Order $order): bool
    {
        // Cannot update completed or cancelled orders
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return false;
        }

        if ($admin->is_super_admin) {
            return true;
        }

        // Check if admin has update permission
        if (!$admin->hasPermissionTo('update orders')) {
            return false;
        }

        // Admin can only update orders from their branch/organization
        return $admin->branch_id === $order->branch_id || 
               $admin->organization_id === $order->organization_id;
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(Admin $admin, Order $order): bool
    {
        // Cannot cancel already completed or cancelled orders
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return false;
        }

        if ($admin->is_super_admin) {
            return true;
        }

        // Check if admin has cancel permission
        if (!$admin->hasPermissionTo('cancel orders')) {
            return false;
        }

        // Admin can only cancel orders from their branch/organization
        return $admin->branch_id === $order->branch_id || 
               $admin->organization_id === $order->organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Order $order): bool
    {
        // Only pending orders can be deleted
        if ($order->status !== Order::STATUS_PENDING) {
            return false;
        }

        if ($admin->is_super_admin) {
            return true;
        }

        return ($admin->hasPermissionTo('delete orders') && 
                ($admin->branch_id === $order->branch_id || 
                 $admin->organization_id === $order->organization_id));
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
