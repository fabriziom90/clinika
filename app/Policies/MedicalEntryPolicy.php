<?php

namespace App\Policies;

use App\Models\MedicalEntry;
use App\Models\User;

class MedicalEntryPolicy
{
    /**
     * Create a new policy instance.
     * VIEW
     * Can only see if:
     * - superadmin
     * - doctor who has appointments with that patient
     * - doctor who created the entry
     * - nurse assigned to an appointment for that patient
     */
    public function view(User $user, MedicalEntry $entry): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        $patientId = $entry->medicalRecord->patient_id;

        // MEDICO
        if ($user->doctor) {
            return
                // Ha creato l'entry
                $entry->doctor_id === $user->doctor->id

                // Oppure ha appuntamenti con quel paziente
                || $user->doctor->appointments()
                    ->where('patient_id', $patientId)
                    ->exists();
        }

        // INFERMIERE
        if ($user->nurse) {
            return $user->nurse->appointments()
                ->where('patient_id', $patientId)
                ->exists();
        }

        return false;
    }

    /**
     * Chi può creare entry
     */
    public function create(User $user): bool
    {
        return $user->doctor;
    }

    /**
     * Chi può modificare entry (versioning: meglio creare nuova entry)
     */
    public function update(User $user, MedicalEntry $entry): bool
    {   
        return $user->doctor && $entry->doctor_id === $user->doctor->id;
    }

    /**
     * Chi può eliminare
     */
    public function delete(User $user, MedicalEntry $entry): bool
    {
        return $user->hasRole('superadmin');
    }
}
