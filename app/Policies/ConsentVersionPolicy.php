<?php

namespace App\Policies;

use App\Models\ConsentVersion;
use App\Models\User;

class ConsentVersionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('consent-version.view');
    }

    public function view(User $user, ConsentVersion $consentVersion): bool
    {
        return $user->can('consent-version.view');
    }

    public function create(User $user): bool
    {
        return $user->can('consent-version.create');
    }

    public function update(User $user, ConsentVersion $consentVersion): bool
    {
        return $user->can('consent-version.update');
    }

    public function delete(User $user, ConsentVersion $consentVersion): bool
    {
        return $user->can('consent-version.delete');
    }
}
