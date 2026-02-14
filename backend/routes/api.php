<?php

declare(strict_types=1);

use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\SwaggerController;
use App\Http\Controllers\StarWarsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => response()->json([
    'name' => 'SWAPI Proxy API',
    'docs' => '/api/docs',
    'endpoints' => [
        'health' => '/api/health',
        'swapi_person' => '/api/swapi/people/{id}',
        'swapi_film' => '/api/swapi/films/{id}',
        'statistics' => '/api/statistics',
    ],
]));

Route::get('/docs/openapi.yaml', [SwaggerController::class, 'spec']);
Route::get('/docs', [SwaggerController::class, 'ui']);

Route::get('/health', HealthController::class);

Route::get('/swapi/people', [StarWarsController::class, 'searchPeople']);
Route::get('/swapi/people/{id}', [StarWarsController::class, 'getPerson'])
    ->whereNumber('id');

Route::get('/swapi/films', [StarWarsController::class, 'searchFilms']);
Route::get('/swapi/films/{id}', [StarWarsController::class, 'getFilm'])
    ->whereNumber('id');

Route::get('/statistics', StatisticsController::class);
