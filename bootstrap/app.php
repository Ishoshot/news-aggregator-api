<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(fn (ThrottleRequestsException $e): JsonResponse => response()->json([
            'message' => 'Too many requests. Please wait and try again later.',
            'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
            'tip' => 'Consider slowing down the requests or batching them to avoid hitting the rate limit.',
        ], 429));
    })->create();
