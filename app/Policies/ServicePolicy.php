<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    // public function before(User $user, $ability)
    // {
    //     if ($user->hasRole('superadmin')) {
    //         return true;
    //     }
    // }

    public function viewAny(User $user): bool
    {
        return $user->can('service.view');
    }

    public function view(User $user, Service $service): bool
    {
        return $user->can('service.view');
    }

    public function create(User $user): bool
    {
        return $user->can('service.create');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->can('service.update');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->can('service.delete');
    }
}
