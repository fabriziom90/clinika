<?php

namespace App\Policies;

use App\Models\PatientHealthHistory;
use App\Models\User;

class PatientHealthHistoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('patient-health-history.view');
    }

    public function view(User $user, PatientHealthHistory $patientHealthHistory): bool
    {
        return $user->can('patient-health-history.view');
    }

    public function create(User $user): bool
    {
        return $user->can('patient-health-history.create');
    }

    public function update(User $user, PatientHealthHistory $patientHealthHistory): bool
    {
        return $user->can('patient-health-history.update');
    }

    public function delete(User $user, PatientHealthHistory $patientHealthHistory): bool
    {
        return $user->can('patient-health-history.delete');
    }
}
