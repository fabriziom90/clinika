<?php

namespace App\Observers;

use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Models\Audit;

class PatientObserver
{
    /**
     * Handle the Patient "created" event.
     */
    public function created(Patient $patient): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'created',
            'auditable_type' => get_class($patient),
            'auditable_id' => $patient->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $patient->toArray(),
        ]);
    }

    /**
     * Handle the Patient "updated" event.
     */
    public function updated(Patient $patient): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'updated',
            'auditable_type' => get_class($patient),
            'auditable_id' => $patient->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $patient->getOriginal(),
            'new_values' => $patient->getChanges(),
        ]);
    }

    /**
     * Handle the Patient "deleted" event.
     */
    public function deleted(Patient $patient): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'deleted',
            'auditable_type' => get_class($patient),
            'auditable_id' => $patient->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $patient->toArray(),
            'new_values' => [],
        ]);
    }

    public function viewed(Patient $patient): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'viewed',
            'auditable_type' => get_class($patient),
            'auditable_id' => $patient->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    /**
     * Handle the Patient "restored" event.
     */
    public function restored(Patient $patient): void
    {
        //
    }

    /**
     * Handle the Patient "force deleted" event.
     */
    public function forceDeleted(Patient $patient): void
    {
        //
    }
}
