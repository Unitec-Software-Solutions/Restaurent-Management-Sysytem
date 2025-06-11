<?php

namespace App\Policies;

use App\Models\{User, Role};

class RolePolicy
{
    public function manage(User $user, Role $role)
    {
        return $user->organization_id === $role->organization_id;
    }
}
