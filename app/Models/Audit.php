<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Audit extends Model
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
