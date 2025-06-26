<?php

namespace App\Policies;

use App\Models\{User, Role};

class RolePolicy
{
    public function manage(User $user, Role $role)
    {
        return $user->organization_id === $role->organization_id;
    }
    public function viewAny($user)
    {
        return $user->is_super_admin;
    }
    public function create($user)
    {
        return $user->is_super_admin; 
    }
    public function update($user, $role)
    {
        
        return $user->is_super_admin || $user->organization_id === $role->organization_id;
    }
    public function view($user, $role)
    {       
        return $user->is_super_admin || $user->organization_id === $role->organization_id;
    }
    public function delete($user, $role)
    {
        return $user->is_super_admin || $user->organization_id === $role->organization_id;
    }
}
