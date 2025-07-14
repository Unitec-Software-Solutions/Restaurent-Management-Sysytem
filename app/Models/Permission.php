<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_permissions');
    }

    public static function getSystemPermissions(): array
    {
        return [
            // System Level
            'system' => [
                'manage_system' => 'Full system administration',
                'manage_organizations' => 'Create and manage organizations',
                'manage_subscription_plans' => 'Manage subscription plans',
                'view_system_reports' => 'View system-wide reports'
            ],

            // Organization Level  
            'organization' => [
                'manage_organization' => 'Manage organization settings',
                'manage_branches' => 'Create and manage branches',
                'manage_users' => 'Manage organization users',
                'manage_subscription' => 'Manage organization subscription',
                'view_reports' => 'View organization reports'
            ],

            // Branch Level
            'branch' => [
                'manage_branch_operations' => 'Manage branch operations',
                'manage_branch_staff' => 'Manage branch staff',
                'manage_inventory' => 'Manage branch inventory',
                'manage_menu' => 'Manage branch menu',
                'manage_orders' => 'Manage orders',
                'manage_reservations' => 'Manage reservations',
                'view_branch_reports' => 'View branch reports'
            ],

            // Staff Level
            'staff' => [
                'view_menu' => 'View menu items',
                'create_orders' => 'Create customer orders',
                'manage_assigned_tasks' => 'Manage assigned tasks',
                'view_assigned_orders' => 'View assigned orders'
            ],

            // Guest Level
            'guest' => [
                'view_public_menu' => 'View public menu',
                'create_guest_orders' => 'Create orders as guest',
                'create_reservations' => 'Create table reservations'
            ]
        ];
    }
}