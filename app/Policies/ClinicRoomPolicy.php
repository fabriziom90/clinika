<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ClinicRoom;

class ClinicRoomPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user): bool
    {
        return $user->can('clinic-room.view');
    }

    public function view(User $user, ClinicRoom $clinicRoom): bool
    {
        return $user->can('clinic-room.view');
    }

    public function create(User $user): bool
    {
        return $user->can('clinic-room.create');
    }

    public function update(User $user, ClinicRoom $clinicRoom): bool
    {
        return $user->can('clinic-room.update');
    }

    public function delete(User $user, ClinicRoom $clinicRoom): bool
    {
        return $user->can('clinic-room.delete');
    }
}
