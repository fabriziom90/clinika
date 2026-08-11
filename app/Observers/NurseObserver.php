<?php

namespace App\Observers;

use App\Models\Nurse;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Models\Audit;

class NurseObserver
{
    /**
     * Handle the Nurse "created" event.
     */
    public function created(Nurse $nurse): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'created',
            'auditable_type' => get_class($nurse),
            'auditable_id' => $nurse->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $nurse->toArray(),
        ]);
    }

    /**
     * Handle the Nurse "updated" event.
     */
    public function updated(Nurse $nurse): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'updated',
            'auditable_type' => get_class($nurse),
            'auditable_id' => $nurse->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $nurse->toArray(),
        ]);
    }

    /**
     * Handle the Nurse "deleted" event.
     */
    public function deleted(Nurse $nurse): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'deleted',
            'auditable_type' => get_class($nurse),
            'auditable_id' => $nurse->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $nurse->toArray(),
        ]);
    }

    public function viewed(Nurse $nurse): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'viewed',
            'auditable_type' => get_class($nurse),
            'auditable_id' => $nurse->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    /**
     * Handle the Nurse "restored" event.
     */
    public function restored(Nurse $nurse): void
    {
        //
    }

    /**
     * Handle the Nurse "force deleted" event.
     */
    public function forceDeleted(Nurse $nurse): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'deleted',
            'auditable_type' => get_class($nurse),
            'auditable_id' => $nurse->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    public function sendResetEmail(Nurse $nurse): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'send reset email',
            'auditable_type' => get_class($nurse),
            'auditable_id' => $nurse->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }
}
