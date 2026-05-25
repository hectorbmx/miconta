<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyN8NSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedSecret = config('services.n8n.webhook_secret');
        $providedSecret = $request->header('X-N8N-SECRET');

        if (! is_string($expectedSecret) || $expectedSecret === '') {
            return response()->json([
                'message' => 'N8N secret is not configured.',
            ], 500);
        }

        if (! is_string($providedSecret) || ! hash_equals($expectedSecret, $providedSecret)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
