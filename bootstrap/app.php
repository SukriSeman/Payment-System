<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Response;

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

        $exceptions->render(function (Throwable $e, $request) {

            // API request → return JSON
            if ($request->is('api/*')) {
                $code = $e->getCode();

                if (!is_int($code) || $code < 100 || $code > 599) {
                    $code = 500;
                }

                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], $code);
            }

            // Web request → use views or redirects
            if ($e instanceof Illuminate\Auth\AuthenticationException) {
                return redirect()->guest(route('login'));
            }

            // Other web errors (optional)
            if ($e instanceof ModelNotFoundException) {
                return response()->view('errors.404', [], 404);
            }

            // fallback
            return response()->view('errors.500', [], 500);
        });
    })->create();
