<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Events\FilmQueryExecuted;
use App\Application\Events\QueryExecuted;
use App\Application\Events\SearchQueryExecuted;
use App\Application\Listeners\RecordFilmQueryMetrics;
use App\Application\Listeners\RecordQueryMetrics;
use App\Application\Listeners\RecordSearchMetrics;
use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use App\Domain\Swapi\Contracts\SwapiClientInterface;
use App\Infrastructure\Logging\LaravelAppLogger;
use App\Infrastructure\Clients\Swapi\SwapiHttpClients;
use App\Infrastructure\Repositories\RedisQueryLogRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SwapiClientInterface::class, function (): SwapiHttpClients {
            return new SwapiHttpClients(
                baseUrl: (string) config('services.swapi.base_url'),
                timeout: (int) config('services.swapi.timeout'),
                retryTimes: (int) config('services.swapi.retry_times'),
                retrySleep: (int) config('services.swapi.retry_sleep'),
                circuitFailureThreshold: (int) config('services.swapi.circuit_failure_threshold'),
                circuitTimeoutSeconds: (int) config('services.swapi.circuit_timeout_seconds'),
                circuitHalfOpenSuccessThreshold: (int) config('services.swapi.circuit_half_open_success_threshold'),
                logger: $this->app->make(AppLoggerInterface::class),
            );
        });

        $this->app->bind(QueryLogRepositoryInterface::class, RedisQueryLogRepository::class);
        $this->app->bind(AppLoggerInterface::class, LaravelAppLogger::class);
    }

    public function boot(): void
    {
        Event::listen(QueryExecuted::class, RecordQueryMetrics::class);
        Event::listen(FilmQueryExecuted::class, RecordFilmQueryMetrics::class);
        Event::listen(SearchQueryExecuted::class, RecordSearchMetrics::class);
    }
}
