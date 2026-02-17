<?php

declare(strict_types=1);

use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use Tests\Mocks\StatisticsMocks;

it('returns statistics from cache', function (): void {
    /** @var \Tests\TestCase $this */
    $popularHours = array_map(fn(int $h) => ['hour' => $h, 'total_count' => 0], range(0, 23));
    $popularHours[14] = ['hour' => 14, 'total_count' => 28];

    $cached = StatisticsMocks::queryStatisticsDto([
        'averageResponseTimeMs' => 150.5,
        'popularHours' => $popularHours,
        'totalQueries' => 20,
        'computedAt' => '2026-01-01T00:00:00+00:00',
        'topSearchQueries' => [
            ['search_type' => 'people', 'query' => 'luke', 'count' => 8, 'percentage' => 40.0],
        ],
    ]);

    $mockRepo = Mockery::mock(QueryLogRepositoryInterface::class);
    $mockRepo->shouldReceive('getCachedStatistics')->once()->andReturn($cached);

    $this->app->instance(QueryLogRepositoryInterface::class, $mockRepo);

    $response = $this->getJson('/api/statistics');

    $response
        ->assertOk()
        ->assertJsonPath('data.total_queries', 20)
        ->assertJsonPath('data.average_response_time_ms', 150.5)
        ->assertJsonPath('data.popular_hours.14.hour', 14)
        ->assertJsonPath('data.popular_hours.14.total_count', 28)
        ->assertJsonPath('data.top_search_queries.0.search_type', 'people')
        ->assertJsonPath('data.top_search_queries.0.query', 'luke')
        ->assertJsonStructure([
            'data' => [
                'top_search_queries',
                'average_response_time_ms',
                'popular_hours',
                'total_queries',
                'computed_at',
            ],
        ]);
});

it('computes statistics when cache is empty', function (): void {
    /** @var \Tests\TestCase $this */
    $stats = StatisticsMocks::queryStatisticsDto(['totalQueries' => 0]);

    $mockRepo = Mockery::mock(QueryLogRepositoryInterface::class);
    $mockRepo->shouldReceive('getCachedStatistics')->once()->andReturnNull();
    $mockRepo->shouldReceive('computeStatistics')->once()->andReturn($stats);
    $mockRepo->shouldReceive('cacheStatistics')->once()->with(Mockery::on(fn ($arg) => $arg->totalQueries === 0));

    $this->app->instance(QueryLogRepositoryInterface::class, $mockRepo);

    $response = $this->getJson('/api/statistics');

    $response
        ->assertOk()
        ->assertJsonPath('data.total_queries', 0)
        ->assertJsonStructure([
            'data' => [
                'top_search_queries',
                'popular_hours',
                'average_response_time_ms',
                'total_queries',
                'computed_at',
            ],
        ]);
});
