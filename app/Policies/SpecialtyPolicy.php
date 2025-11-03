<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Specialty;

class SpecialtyPolicy
{
    // public function before(User $user, $ability)
    // {
    //     if ($user->hasRole('superadmin')) {
    //         return true;
    //     }
    // }

    public function viewAny(User $user): bool
    {
        return $user->can('specialty.view');
    }

    public function view(User $user, Specialty $specialty): bool
    {
        return $user->can('specialty.view');
    }

    public function create(User $user): bool
    {
        return $user->can('specialty.create');
    }

    public function update(User $user, Specialty $specialty): bool
    {
        return $user->can('specialty.update');
    }

    public function delete(User $user, Specialty $specialty): bool
    {
        return $user->can('specialty.delete');
    }
}
