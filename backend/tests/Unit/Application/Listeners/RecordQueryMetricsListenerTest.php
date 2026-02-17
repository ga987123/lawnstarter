<?php

declare(strict_types=1);

use App\Application\Events\QueryExecuted;
use App\Application\Listeners\RecordQueryMetrics;
use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;

it('calls repository recordQuery with event data', function (): void {
    /** @var \Tests\TestCase $this */
    $repo = Mockery::mock(QueryLogRepositoryInterface::class);
    $repo->shouldReceive('recordQuery')->once()->with(1, 150.5);

    $logger = Mockery::mock(AppLoggerInterface::class);
    $logger->shouldReceive('info')->once();

    $this->app->instance(QueryLogRepositoryInterface::class, $repo);
    $this->app->instance(AppLoggerInterface::class, $logger);

    $listener = $this->app->make(RecordQueryMetrics::class);
    $listener->handle(new QueryExecuted(1, 150.5));
});
