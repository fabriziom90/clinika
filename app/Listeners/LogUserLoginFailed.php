<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\DB;

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

        DB::connection($connection)->table('audits')->insert([
            'user_id' => null,
            'event' => 'login_failed',
            'auditable_type' => $event->user ? get_class($event->user) : null,
            'auditable_id' => $event->user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => json_encode([
                'email' => $event->credentials['email'] ?? null,
            ]),
            'new_values' => json_encode([]),
        ]);
    }
}
