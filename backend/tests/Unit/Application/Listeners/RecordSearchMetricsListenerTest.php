<?php

declare(strict_types=1);

use App\Application\Events\SearchQueryExecuted;
use App\Application\Listeners\RecordSearchMetrics;
use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;

it('calls repository recordSearchQuery with event data', function (): void {
    /** @var \Tests\TestCase $this */
    $repo = Mockery::mock(QueryLogRepositoryInterface::class);
    $repo->shouldReceive('recordSearchQuery')->once()->with('people', 'luke', 100.5, 3);

    $logger = Mockery::mock(AppLoggerInterface::class);
    $logger->shouldReceive('info')->once();

    $this->app->instance(QueryLogRepositoryInterface::class, $repo);
    $this->app->instance(AppLoggerInterface::class, $logger);

    $listener = $this->app->make(RecordSearchMetrics::class);
    $listener->handle(new SearchQueryExecuted('people', 'luke', 100.5, 3));
});
