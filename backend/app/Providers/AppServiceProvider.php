<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Events\QueryExecuted;
use App\Application\Listeners\RecordQueryMetrics;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use App\Domain\Swapi\Contracts\SwapiGatewayInterface;
use App\Infrastructure\Gateways\SwapiHttpGateway;
use App\Infrastructure\Repositories\RedisQueryLogRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SwapiGatewayInterface::class, fn (): SwapiHttpGateway => new SwapiHttpGateway(
            baseUrl: (string) config('services.swapi.base_url'),
            timeout: (int) config('services.swapi.timeout'),
            retryTimes: (int) config('services.swapi.retry_times'),
            retrySleep: (int) config('services.swapi.retry_sleep'),
        ));

        $this->app->bind(QueryLogRepositoryInterface::class, RedisQueryLogRepository::class);
    }

    public function boot(): void
    {
        Event::listen(QueryExecuted::class, RecordQueryMetrics::class);
    }
}
