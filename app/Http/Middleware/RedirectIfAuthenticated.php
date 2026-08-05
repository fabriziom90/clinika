<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {

        $guard = $request->attributes->get('clinic')
           ? 'web'
           : 'superadmin';

        if (Auth::guard($guard)->check()) {
            return redirect()->route(
                $guard === 'web'
                    ? 'admin.dashboard'
                    : 'superadmin.clinics.index'
            );
        }

        return $next($request);
    }
}
