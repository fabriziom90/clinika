<?php

namespace App\Observers;

use App\Models\Secretary;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Audit;

class SecretaryObserver
{
    /**
     * Handle the Secretary "created" event.
     */
    public function created(Secretary $secretary): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'created',
            'auditable_type' => get_class($secretary),
            'auditable_id' => $secretary->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $secretary->toArray(),
        ]);
    }

    /**
     * Handle the Secretary "updated" event.
     */
    public function updated(Secretary $secretary): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'updated',
            'auditable_type' => get_class($secretary),
            'auditable_id' => $secretary->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $secretary->toArray(),
        ]);
    }

    /**
     * Handle the Secretary "deleted" event.
     */
    public function deleted(Secretary $secretary): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'deleted',
            'auditable_type' => get_class($secretary),
            'auditable_id' => $secretary->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $secretary->toArray(),
        ]);
    }

    public function viewed(Secretary $secretary): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'viewed',
            'auditable_type' => get_class($secretary),
            'auditable_id' => $secretary->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    /**
     * Handle the Secretary "restored" event.
     */
    public function restored(Secretary $secretary): void
    {
        //
    }

    /**
     * Handle the Secretary "force deleted" event.
     */
    public function forceDeleted(Secretary $secretary): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'deleted',
            'auditable_type' => get_class($secretary),
            'auditable_id' => $secretary->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    public function sendResetEmail(Secretary $secretary): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'send reset email',
            'auditable_type' => get_class($secretary),
            'auditable_id' => $secretary->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }
}
