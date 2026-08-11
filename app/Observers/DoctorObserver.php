<?php

namespace App\Observers;

use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Models\Audit;

class DoctorObserver
{
    /**
     * Handle the Doctor "created" event.
     */
    public function created(Doctor $doctor): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'created',
            'auditable_type' => get_class($doctor),
            'auditable_id' => $doctor->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $doctor->toArray(),
        ]);
    }

    /**
     * Handle the Doctor "updated" event.
     */
    public function updated(Doctor $doctor): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'updated',
            'auditable_type' => get_class($doctor),
            'auditable_id' => $doctor->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $doctor->toArray(),
        ]);
    }

    /**
     * Handle the Doctor "deleted" event.
     */
    public function deleted(Doctor $doctor): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'deleted',
            'auditable_type' => get_class($doctor),
            'auditable_id' => $doctor->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $doctor->toArray(),
        ]);
    }

    public function viewed(Doctor $doctor): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'viewed',
            'auditable_type' => get_class($doctor),
            'auditable_id' => $doctor->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    /**
     * Handle the Doctor "restored" event.
     */
    public function restored(Doctor $doctor): void
    {
        //
    }

    /**
     * Handle the Doctor "force deleted" event.
     */
    public function forceDeleted(Doctor $doctor): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'deleted',
            'auditable_type' => get_class($doctor),
            'auditable_id' => $doctor->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    public function sendResetEmail(Doctor $doctor): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'send reset email',
            'auditable_type' => get_class($doctor),
            'auditable_id' => $doctor->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }
}
