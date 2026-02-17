<?php

declare(strict_types=1);

use App\Application\Events\FilmQueryExecuted;
use App\Application\Listeners\RecordFilmQueryMetrics;
use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;

it('calls repository recordFilmQuery with event data', function (): void {
    /** @var \Tests\TestCase $this */
    $repo = Mockery::mock(QueryLogRepositoryInterface::class);
    $repo->shouldReceive('recordFilmQuery')->once()->with(2, 200.0);

    $logger = Mockery::mock(AppLoggerInterface::class);
    $logger->shouldReceive('info')->once();

    $this->app->instance(QueryLogRepositoryInterface::class, $repo);
    $this->app->instance(AppLoggerInterface::class, $logger);

    $listener = $this->app->make(RecordFilmQueryMetrics::class);
    $listener->handle(new FilmQueryExecuted(2, 200.0));
});
