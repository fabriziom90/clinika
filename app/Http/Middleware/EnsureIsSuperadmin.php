<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Il guard "superadmin" (auth:superadmin) verifica solo che esista una
 * sessione valida su un CentralUser - non distingue in base al flag
 * is_superadmin, che oggi è usato solo per decorare l'interfaccia
 * (HandleInertiaRequests lo espone al frontend) senza proteggere nulla lato
 * server. Questo middleware chiude quel buco.
 */
class EnsureIsSuperadmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('superadmin');

        abort_unless($user && $user->is_superadmin, 403);

        return $next($request);
    }
}
