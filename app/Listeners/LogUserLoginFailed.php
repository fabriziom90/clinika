<?php

namespace App\Listeners;

use App\Models\Audit;
use Illuminate\Auth\Events\Failed;

class LogUserLoginFailed
{
    public function __construct()
    {
        //
    }

    public function handle(Failed $event): void
    {
        $connection = $event->guard === 'superadmin'
        ? 'central'
        : 'tenant';

        Audit::on($connection)->forceCreate([
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
