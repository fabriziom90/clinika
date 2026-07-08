<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    /**
     * Create a new policy instance.
     */
    // public function view(User $user, MedicalRecord $medicalRecord): bool
    // {
    //     if ($user->hasRole('superadmin')) {
    //         return true;
    //     }

    //     $patientId = $medicalRecord->patient_id;

    //     // MEDICO
    //     if ($user->doctor) {

    //         // Ha appuntamenti con il paziente
    //         $hasAppointment = $user->doctor->appointments()
    //             ->where('patient_id', $patientId)
    //             ->exists();

    //         // Ha creato almeno una entry per questo paziente
    //         $hasEntry = $medicalRecord->medicalEntries()
    //             ->where('doctor_id', $user->doctor->id)
    //             ->exists();

    //         return $hasAppointment || $hasEntry;
    //     }

    //     // INFERMIERE
    //     if ($user->nurse) {
    //         return $user->nurse->appointments()
    //             ->where('patient_id', $patientId)
    //             ->exists();
    //     }

    //     return false;
    // }
    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->can('medical-record.view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        return false;
    }

    public function delete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->hasRole('superadmin');
    }
}
