<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Branch;

class BranchPolicy
{
    /**
     * Grant all permissions to super admin before checking other methods.
     */
    public function before($user, $ability)
    {
        if (isset($user->is_superadmin) && $user->is_superadmin) {
            return true;
        }
    }

    public function view($user, Branch $branch)
    {
        return $user->organization_id === $branch->organization_id;
    }

    public function update(Admin $admin, Branch $branch)
    {
        return $admin->organization_id === $branch->organization_id;
    }
}