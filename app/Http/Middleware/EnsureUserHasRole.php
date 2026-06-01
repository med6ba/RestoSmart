<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! $request->user()->hasAnyRole($roles)) {
            abort(403);
        }

        if (function_exists('tenancy') && tenancy()->initialized && $request->user()->role !== 'super') {
            abort_unless($request->user()->tenant_id === tenant('id'), 403);
        }

        return $next($request);
    }
}
