<?php

namespace App\Policies;

use App\Models\ConsentType;
use App\Models\User;

class ConsentTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('consent-type.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ConsentType $consentType): bool
    {
        return $user->can('consent-type.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('consent-type.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ConsentType $consentType): bool
    {
        return $user->can('consent-type.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ConsentType $consentType): bool
    {
        return $user->can('consent-type.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ConsentType $consentType): bool
    {
        return $user->can('consent-type.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ConsentType $consentType): bool
    {
        return $user->can('consent-type.forceDelete');
    }
}
