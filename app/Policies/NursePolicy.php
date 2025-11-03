<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Nurse;

class NursePolicy
{   
    // public function before(User $user, $ability)
    // {
    //     if ($user->hasRole('superadmin')) {
    //         return true;
    //     }
    // }

    public function viewAny(User $user): bool
    {
        return $user->can('nurse.view');
    }

    public function view(User $user, Nurse $nurse): bool
    {
        return $user->can('nurse.view');
    }

    public function create(User $user): bool
    {
        return $user->can('nurse.create');
    }

    public function update(User $user, Nurse $nurse): bool
    {
        return $user->can('nurse.update');
    }

    public function delete(User $user, Nurse $nurse): bool
    {
        return $user->can('nurse.delete');
    }
}
