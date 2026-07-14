<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $tenant = TenantService::resolveFromHost($host);

        TenantService::set($tenant);

        if ($tenant) {
            view()->share('currentTenant', $tenant);
        }

        return $next($request);
    }
}
