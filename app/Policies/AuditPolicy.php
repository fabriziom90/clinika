<?php

namespace App\Policies;

use App\Models\User;
use OwenIt\Auditing\Models\Audit;

class AuditPolicy
{
    /**
     * Determine whether the user can view any audit logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can view a specific audit log.
     */
    public function view(User $user, Audit $audit): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can create audit logs.
     * (Di solito non serve, lasciamo false)
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update audit logs.
     * (Mai)
     */
    public function update(User $user, Audit $audit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete audit logs.
     */
    public function delete(User $user, Audit $audit): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can restore audit logs.
     */
    public function restore(User $user, Audit $audit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete audit logs.
     */
    public function forceDelete(User $user, Audit $audit): bool
    {
        return false;
    }
}
