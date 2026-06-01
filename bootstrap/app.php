<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->routeIs('tenant.admin', 'tenant.admin.*')) {
                return route('login');
            }

            if ($request->routeIs('tenant.*')) {
                $tenant = $request->route('tenant') ?: (function_exists('tenant') ? tenant('id') : null);
                $tenantId = is_object($tenant) && method_exists($tenant, 'getKey') ? $tenant->getKey() : $tenant;

                return $tenantId ? route('tenant.login', $tenantId) : route('login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function (Request $request): string {
            if ($request->routeIs('tenant.*')) {
                $tenant = $request->route('tenant') ?: (function_exists('tenant') ? tenant('id') : null);
                $tenantId = is_object($tenant) && method_exists($tenant, 'getKey') ? $tenant->getKey() : $tenant;

                return $tenantId ? route('tenant.dashboard', $tenantId) : route('dashboard');
            }

            $user = $request->user();

            if ($user?->tenant_id && $user->hasAnyRole(['kitchen', 'driver', 'client'])) {
                return route('tenant.dashboard', $user->tenant_id);
            }

            return route('dashboard');
        });

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
