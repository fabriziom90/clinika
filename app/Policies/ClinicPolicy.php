<?php

namespace App\Policies;

use App\Models\CentralUser;
use App\Models\Clinic;

class ClinicPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(CentralUser $user): bool
    {
        return $user->can('clinic.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(CentralUser $user, Clinic $clinic): bool
    {
        return $user->can('clinic.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(CentralUser $user): bool
    {
        return $user->can('clinic.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(CentralUser $user, Clinic $clinic): bool
    {
        return $user->can('clinic.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(CentralUser $user, Clinic $clinic): bool
    {
        return $user->can('clinic.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(CentralUser $user, Clinic $clinic): bool
    {
        return $user->can('clinic.restore');
    }
}
