<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Doctor;

class DoctorPolicy
{
    // public function before(User $user, $ability)
    // {
    //     if ($user->hasRole('superadmin')) {
    //         return true;
    //     }
    // }

    public function viewAny(User $user): bool
    {
        return $user->can('doctor.view');
    }

    public function view(User $user, Doctor $doctor): bool
    {
        return $user->can('doctor.view');
    }

    public function create(User $user): bool
    {
        return $user->can('doctor.create');
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return $user->can('doctor.update');
    }

    public function delete(User $user, Doctor $doctor): bool
    {
        return $user->can('doctor.delete');
    }

}
