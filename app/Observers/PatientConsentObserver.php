<?php

namespace App\Observers;

use App\Models\PatientConsent;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Models\Audit;

class PatientConsentObserver
{   
    public function viewed(PatientConsent $patientConsent): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'viewed',
            'auditable_type' => get_class($patientConsent),
            'auditable_id' => $patientConsent->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    /**
     * Handle the PatientConsent "created" event.
     */
    public function created(): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'created',
            'auditable_type' => 'App\Models\PatientConsent',
            'auditable_id' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    /**
     * Handle the PatientConsent "updated" event.
     */
    public function updated(): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'updated',
            'auditable_type' => 'App\Models\PatientConsent',
            'auditable_id' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    /**
     * Handle the PatientConsent "deleted" event.
     */
    public function deleted(PatientConsent $patientConsent): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'deleted',
            'auditable_type' => get_class($patientConsent),
            'auditable_id' => $patientConsent->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    /**
     * Handle the PatientConsent "restored" event.
     */
    public function restored(PatientConsent $patientConsent): void
    {
        
    }

    /**
     * Handle the PatientConsent "force deleted" event.
     */
    public function forceDeleted(PatientConsent $patientConsent): void
    {
        //
    }
}
