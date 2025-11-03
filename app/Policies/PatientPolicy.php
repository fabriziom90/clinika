<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patient;

class PatientPolicy
{
    // public function before(User $user, $ability)
    // {
    //     if ($user->hasRole('superadmin')) {
    //         return true;
    //     }
    // }

    public function viewAny(User $user): bool
    {
        return $user->can('patient.view');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->can('patient.view');
    }

    public function create(User $user): bool
    {
        return $user->can('patient.create');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->can('patient.update');
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->can('patient.delete');
    }

}
