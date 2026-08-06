<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Models\Audit as AuditModel;

class Audit extends AuditModel
{
    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    public function getConnectionName()
    {
        if (Auth::guard('superadmin')->check()) {
            return 'central';
        }

        if (Auth::guard('web')->check() && app()->bound('currentClinic')) {
            return 'tenant';
        }

        return parent::getConnectionName();
    }
}
