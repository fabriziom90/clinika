<?php

namespace App\Policies;

use App\Models\Secretary;
use App\Models\User;

class SecretaryPolicy
{
    // public function before(User $user, $ability)
    // {
    //     if ($user->hasRole('superadmin')) {
    //         return true;
    //     }
    // }

    public function viewAny(User $user): bool
    {
        return $user->can('secretary.view');
    }

    public function view(User $user, Secretary $secretary): bool
    {
        return $user->can('secretary.view');
    }

    public function create(User $user): bool
    {
        return $user->can('secretary.create');
    }

    public function update(User $user, Secretary $secretary): bool
    {
        return $user->can('secretary.update');
    }

    public function delete(User $user, Secretary $secretary): bool
    {
        return $user->can('secretary.delete');
    }
}
