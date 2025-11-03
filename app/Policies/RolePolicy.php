<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;

class RolePolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->can('role.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('role.view');
    }

    public function create(User $user): bool
    {
        return $user->can('role.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('role.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('role.delete');
    }
}

