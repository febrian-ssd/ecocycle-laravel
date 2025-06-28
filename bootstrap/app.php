<?php
// bootstrap/app.php - COMPLETE UPDATE

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'user' => \App\Http\Middleware\UserMiddleware::class,
        ]);

        // Add middleware to groups
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\UpdateLastSeenStatus::class,
        ]);

        // API-specific middleware
        $middleware->appendToGroup('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Global middleware for API throttling
        $middleware->group('api', [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Handle API exceptions with consistent format
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint not found',
                    'data' => null
                ], 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed',
                    'data' => null
                ], 405);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => null,
                    'errors' => $e->errors()
                ], 422);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'data' => null
                ], 404);
            }
        });

        // Generic API exception handler
        $exceptions->render(function (\Exception $e, Request $request) {
            if ($request->is('api/*')) {
                // Don't override specific exceptions that are already handled
                if ($e instanceof NotFoundHttpException ||
                    $e instanceof MethodNotAllowedHttpException ||
                    $e instanceof ValidationException ||
                    $e instanceof ModelNotFoundException) {
                    return null; // Let the specific handlers deal with it
                }

                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return response()->json([
                    'success' => false,
                    'message' => app()->environment('production')
                        ? 'Something went wrong'
                        : $e->getMessage(),
                    'data' => null
                ], $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500);
            }
        });

    })->create();
