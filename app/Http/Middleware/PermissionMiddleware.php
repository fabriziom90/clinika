<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permissions)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Non autenticato.');
        }

        // supporta permessi multipli separati da |
        $permissionList = explode('|', $permissions);

        foreach ($permissionList as $permission) {
            if ($user->hasPermissionTo(trim($permission))) {
                return $next($request);
            }
        }

        abort(403, 'Non autorizzato.');
    }
}
