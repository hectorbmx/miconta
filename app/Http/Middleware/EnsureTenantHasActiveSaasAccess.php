<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantHasActiveSaasAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = $user?->tenant;

        if (! $tenant) {
            abort(403, 'Tu usuario no tiene un cliente SaaS asignado.');
        }

        if ($tenant->hasActiveSaasAccess()) {
            return $next($request);
        }

        return redirect()->route('client.billing.pending');
    }
}
