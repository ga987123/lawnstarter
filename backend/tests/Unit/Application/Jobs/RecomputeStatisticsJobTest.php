<?php

declare(strict_types=1);

use App\Application\Jobs\RecomputeStatisticsJob;
use App\Application\Services\StatisticsService;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use Tests\Mocks\StatisticsMocks;

it('handle calls statistics service recompute once', function (): void {
    /** @var \Tests\TestCase $this */
    $dto = StatisticsMocks::queryStatisticsDto();
    $repo = Mockery::mock(QueryLogRepositoryInterface::class);
    $repo->shouldReceive('computeStatistics')->once()->andReturn($dto);
    $repo->shouldReceive('cacheStatistics')->once();

    $this->app->instance(QueryLogRepositoryInterface::class, $repo);

    $service = $this->app->make(StatisticsService::class);
    $job = new RecomputeStatisticsJob();
    $job->handle($service);
});
