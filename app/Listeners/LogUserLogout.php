<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use OwenIt\Auditing\Models\Audit;

class LogUserLogout
{
    public function __construct()
    {
        //
    }

    public function handle(Logout $event): void
    {
        Audit::forceCreate([
            'user_id' => $event->user->id,
            'user_type' => get_class($event->user), // <- qui
            'event' => 'logout',
            'auditable_type' => null,  // login non è su un modello specifico
            'auditable_id' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);
    }
}
