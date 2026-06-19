<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust Render's reverse proxy so HTTPS scheme & secure cookies work.
        // Without this: form action jadi http:// (browser warning) + CSRF 419.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'opportunities/*/move-stage',
            'opportunities/*/quick-update',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\ApplySkin::class,
        ]);

        // crm-skin = preferensi tema (non-sensitif). Plaintext supaya konsisten
        // antara route, middleware, dan pre-paint JS — bukan data rahasia.
        $middleware->encryptCookies(except: ['crm-skin']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
