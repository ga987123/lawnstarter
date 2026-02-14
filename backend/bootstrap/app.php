<?php

declare(strict_types=1);

use App\Domain\Swapi\Exceptions\SwapiNotFoundException;
use App\Domain\Swapi\Exceptions\SwapiUnavailableException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::get('/', fn () => redirect('/api'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            \App\Http\Middleware\RequestLogMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e): void {
            Log::error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        });

        $exceptions->shouldRenderJsonWhen(fn (Request $request) => true);

        $exceptions->render(function (SwapiNotFoundException $e, Request $request) {
            return response()->json([
                'type' => 'https://httpstatuses.com/404',
                'title' => 'Not Found',
                'status' => 404,
                'detail' => $e->getMessage(),
            ], 404);
        });

        $exceptions->render(function (SwapiUnavailableException $e, Request $request) {
            return response()->json([
                'type' => 'https://httpstatuses.com/502',
                'title' => 'Bad Gateway',
                'status' => 502,
                'detail' => $e->getMessage(),
            ], 502);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'type' => 'https://httpstatuses.com/422',
                'title' => 'Validation Error',
                'status' => 422,
                'detail' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'type' => 'https://httpstatuses.com/404',
                'title' => 'Not Found',
                'status' => 404,
                'detail' => $e->getMessage() ?: 'The requested resource was not found.',
            ], 404);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            return response()->json([
                'type' => 'https://httpstatuses.com/'.$e->getStatusCode(),
                'title' => 'Error',
                'status' => $e->getStatusCode(),
                'detail' => $e->getMessage(),
            ], $e->getStatusCode());
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            return response()->json([
                'type' => 'https://httpstatuses.com/500',
                'title' => 'Internal Server Error',
                'status' => 500,
                'detail' => app()->hasDebugModeEnabled()
                    ? $e->getMessage()
                    : 'An unexpected error occurred.',
            ], 500);
        });
    })->create();
