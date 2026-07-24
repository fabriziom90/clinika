<?php

namespace App\Observers;

use App\Models\ConsentVersion;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Models\Audit;


class ConsentVersionObserver
{
    /**
     * Handle the ConsentVersion "created" event.
     */
    public function created(ConsentVersion $consentVersion): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'created',
            'auditable_type' => get_class($consentVersion),
            'auditable_id' => $consentVersion->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $consentVersion->toArray(),
        ]);
    }

    /**
     * Handle the ConsentVersion "updated" event.
     */
    public function updated(ConsentVersion $consentVersion): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'updated',
            'auditable_type' => get_class($consentVersion),
            'auditable_id' => $consentVersion->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $consentVersion->toArray(),
        ]);
    }

    /**
     * Handle the ConsentVersion "deleted" event.
     */
    public function deleted(ConsentVersion $consentVersion): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'deleted',
            'auditable_type' => get_class($consentVersion),
            'auditable_id' => $consentVersion->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $consentVersion->toArray(),
        ]);
    }

    public function viewed(ConsentVersion $consentVersion): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'viewed',
            'auditable_type' => get_class($consentVersion),
            'auditable_id' => $consentVersion->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    public function showPdf(ConsentVersion $consentVersion): void
    {
        Audit::forceCreate([
            'user_id' => Auth::id(),
            'user_type' => Auth::user() ? get_class(Auth::user()) : null,
            'event' => 'show pdf',
            'auditable_type' => get_class($consentVersion),
            'auditable_id' => $consentVersion->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }

    /**
     * Handle the ConsentVersion "restored" event.
     */
    public function restored(ConsentVersion $consentVersion): void
    {
        //
    }

    /**
     * Handle the ConsentVersion "force deleted" event.
     */
    public function forceDeleted(ConsentVersion $consentVersion): void
    {
        //
    }
}
