<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('appointment.view');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.view');
    }

    public function create(User $user): bool
    {
        return $user->can('appointment.create');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.update');
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.delete');
    }

    public function changeStatus(User $user, Appointment $appointment): bool
    {
        return $user->can('appointment.change-status');
    }
}
