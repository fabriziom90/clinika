<?php

namespace App\Policies;

use App\Models\ReminderType;
use App\Models\User;

class ReminderTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('reminder-type.view');
    }

    public function view(User $user, ReminderType $reminderType): bool
    {
        return $user->can('reminder-type.view');
    }

    public function create(User $user): bool
    {
        return $user->can('reminder-type.create');
    }

    public function update(User $user, ReminderType $reminderType): bool
    {
        return $user->can('reminder-type.update');
    }

    public function delete(User $user, ReminderType $reminderType): bool
    {
        return $user->can('reminder-type.delete');
    }
}
