<?php

namespace App\Policies;

use App\Models\PatientConsent;
use App\Models\User;

class PatientConsentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('patient-consent.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PatientConsent $patientConsent): bool
    {
        if (! $user->can('patient-consent.view')) {
            return false;
        }

        if ($user->hasRole(['admin', 'secretary'])) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return $patientConsent->patient
                ->doctors()
                ->whereKey($user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('patient-consent.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PatientConsent $patientConsent): bool
    {
        return $user->can('patient-consent.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PatientConsent $patientConsent): bool
    {
        return $user->can('patient-consent.delete');
    }
}
