<?php

use App\Http\Middleware\InternalKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Only the reverse proxy (Caddy -> nginx) can reach PHP, so its forwarded IP/proto headers are trusted.
        $middleware->trustProxies(at: '*');
        $middleware->alias(['internal.key' => InternalKey::class]);
        $middleware->validateCsrfTokens(except: ['deploy-hook']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // No-ops unless SENTRY_DSN (or SENTRY_LARAVEL_DSN) is set — see config/sentry.php.
        Integration::handles($exceptions);
    })->create();
