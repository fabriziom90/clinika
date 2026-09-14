<?php

namespace App\Policies;

use App\Models\AppointmentReminder;
use App\Models\User;

class AppointmentReminderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('appointment-reminder.view');
    }

    public function view(User $user, AppointmentReminder $appointmentReminder): bool
    {
        return $user->can('appointment-reminder.view');
    }
}
