<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('api', [
            // 'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        })->stop();
        $exceptions->report(function (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        })->stop();
    })->create();
