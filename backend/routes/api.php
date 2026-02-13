<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controllers\HealthController;
use App\Infrastructure\Http\Controllers\StatisticsController;
use App\Infrastructure\Http\Controllers\SwaggerController;
use App\Infrastructure\Http\Controllers\SwapiController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'SWAPI Proxy API',
    'docs' => '/api/docs',
    'endpoints' => [
        'health' => '/api/health',
        'swapi_person' => '/api/swapi/people/{id}',
        'statistics' => '/api/statistics',
    ],
]));

Route::get('/docs/openapi.yaml', [SwaggerController::class, 'spec']);
Route::get('/docs', [SwaggerController::class, 'ui']);

Route::get('/health', HealthController::class);

Route::get('/swapi/people', [SwapiController::class, 'searchPeople']);
Route::get('/swapi/people/{id}', [SwapiController::class, 'show'])
    ->whereNumber('id');
Route::get('/swapi/films', [SwapiController::class, 'searchFilms']);

Route::get('/statistics', StatisticsController::class);
