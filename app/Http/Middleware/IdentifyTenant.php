<?php

namespace App\Http\Middleware;

use App\Services\Connection\TenantDatabaseService;
use App\Services\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(
        protected TenantResolver $tenantResolver,
        protected TenantDatabaseService $tenantDatabaseService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $clinic = $this->tenantResolver->resolve($request);

        if (! $clinic) {
            return $next($request);
        }

        $this->tenantDatabaseService->connect($clinic);

        $request->attributes->set('clinic', $clinic);

        app()->instance('currentClinic', $clinic);

        return $next($request);
    }
}
