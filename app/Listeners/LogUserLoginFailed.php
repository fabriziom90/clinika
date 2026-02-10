<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use OwenIt\Auditing\Models\Audit;

class LogUserLoginFailed
{
    public function __construct()
    {
        //
    }

    public function handle(Failed $event): void
    {
        Audit::forceCreate([
            'user_id' => null,
            'event' => 'login_failed',
            'auditable_type' => $event->user ? get_class($event->user) : null,
            'auditable_id' => $event->user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [
                'email' => $event->credentials['email'] ?? null,
            ],
            'new_values' => [],
        ]);
    }
}
