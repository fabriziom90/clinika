<?php

namespace App\Policies;

use App\Models\CentralUser;
use App\Models\Clinic;

class ClinicPolicy
{
    public function viewAny(CentralUser $user): bool
    {
        return $user->is_superadmin;
    }

    public function view(CentralUser $user, Clinic $clinic): bool
    {
        return $user->is_superadmin;
    }

    public function create(CentralUser $user): bool
    {
        return $user->is_superadmin;
    }

    public function update(CentralUser $user, Clinic $clinic): bool
    {
        return $user->is_superadmin;
    }

    public function delete(CentralUser $user, Clinic $clinic): bool
    {
        return $user->is_superadmin;
    }

    public function restore(CentralUser $user, Clinic $clinic): bool
    {
        return $user->is_superadmin;
    }
}
