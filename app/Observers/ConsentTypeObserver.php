<?php

namespace App\Observers;

use App\Models\Audit;
use App\Models\ConsentType;
use Illuminate\Support\Facades\Auth;

class ConsentTypeObserver
{
    /**
     * Handle the ConsentType "created" event.
     */
    public function created(ConsentType $consentType): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'created',
            'auditable_type' => get_class($consentType),
            'auditable_id' => $consentType->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $consentType->toArray(),
        ]);
    }

    /**
     * Handle the ConsentType "updated" event.
     */
    public function updated(ConsentType $consentType): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'updated',
            'auditable_type' => get_class($consentType),
            'auditable_id' => $consentType->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $consentType->toArray(),
        ]);
    }

    /**
     * Handle the ConsentType "deleted" event.
     */
    public function deleted(ConsentType $consentType): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'deleted',
            'auditable_type' => get_class($consentType),
            'auditable_id' => $consentType->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $consentType->toArray(),
        ]);
    }

    public function viewed(ConsentType $consentType): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'viewed',
            'auditable_type' => get_class($consentType),
            'auditable_id' => $consentType->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    /**
     * Handle the ConsentType "restored" event.
     */
    public function restored(ConsentType $consentType): void
    {
        //
    }

    /**
     * Handle the ConsentType "force deleted" event.
     */
    public function forceDeleted(ConsentType $consentType): void
    {
        //
    }
}
