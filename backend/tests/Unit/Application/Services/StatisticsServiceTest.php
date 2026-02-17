<?php

declare(strict_types=1);

use App\Application\Services\StatisticsService;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use App\Domain\Statistics\DTOs\QueryStatisticsDto;
use Tests\Mocks\StatisticsMocks;

it('getStatistics returns cached DTO when cache is hit', function (): void {
    /** @var \Tests\TestCase $this */
    $cached = StatisticsMocks::queryStatisticsDto(['totalQueries' => 42]);

    $repo = Mockery::mock(QueryLogRepositoryInterface::class);
    $repo->shouldReceive('getCachedStatistics')->once()->andReturn($cached);
    $repo->shouldNotReceive('computeStatistics');
    $repo->shouldNotReceive('cacheStatistics');

    $this->app->instance(QueryLogRepositoryInterface::class, $repo);

    $service = $this->app->make(StatisticsService::class);
    $result = $service->getStatistics();

    expect($result)->toBe($cached)->and($result->totalQueries)->toBe(42);
});

it('getStatistics computes and caches when cache is empty', function (): void {
    /** @var \Tests\TestCase $this */
    $computed = StatisticsMocks::queryStatisticsDto(['totalQueries' => 10]);

    $repo = Mockery::mock(QueryLogRepositoryInterface::class);
    $repo->shouldReceive('getCachedStatistics')->once()->andReturn(null);
    $repo->shouldReceive('computeStatistics')->once()->andReturn($computed);
    $repo->shouldReceive('cacheStatistics')->once()->with(Mockery::on(fn ($arg) => $arg === $computed));

    $this->app->instance(QueryLogRepositoryInterface::class, $repo);

    $service = $this->app->make(StatisticsService::class);
    $result = $service->getStatistics();

    expect($result)->toBe($computed)->and($result->totalQueries)->toBe(10);
});

it('recompute returns and caches computed statistics', function (): void {
    /** @var \Tests\TestCase $this */
    $computed = StatisticsMocks::queryStatisticsDto(['totalQueries' => 5]);

    $repo = Mockery::mock(QueryLogRepositoryInterface::class);
    $repo->shouldReceive('computeStatistics')->once()->andReturn($computed);
    $repo->shouldReceive('cacheStatistics')->once()->with($computed);

    $this->app->instance(QueryLogRepositoryInterface::class, $repo);

    $service = $this->app->make(StatisticsService::class);
    $result = $service->recompute();

    expect($result)->toBe($computed);
});
