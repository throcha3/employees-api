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
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {

            // Don't show error stack when in production
            if (app()->isProduction()) {
                $statusCode = ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) ? $e->getStatusCode() : 500;

                return response()->json([
                    'message' => 'Ocorreu um erro inesperado no servidor.',
                    'code' => $statusCode
                ], $statusCode);
            }

            return false;
        });
    })->create();
