<?php

declare(strict_types=1);

use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use App\Domain\Statistics\DTOs\QueryStatisticsDto;

it('returns statistics from cache', function (): void {
    $popularHours = array_map(fn(int $h) => ['hour' => $h, 'total_count' => 0], range(0, 23));
    $popularHours[14] = ['hour' => 14, 'total_count' => 28];

    $mockRepo = Mockery::mock(QueryLogRepositoryInterface::class);
    $mockRepo->shouldReceive('getCachedStatistics')
        ->once()
        ->andReturn(new QueryStatisticsDto(
            averageResponseTimeMs: 150.5,
            popularHours: $popularHours,
            totalQueries: 20,
            computedAt: '2026-01-01T00:00:00+00:00',
            topSearchQueries: [
                ['search_type' => 'people', 'query' => 'luke', 'count' => 8, 'percentage' => 40.0],
            ],
        ));

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
    $popularHours = array_map(fn(int $h) => ['hour' => $h, 'total_count' => 0], range(0, 23));

    $stats = new QueryStatisticsDto(
        averageResponseTimeMs: 0.0,
        popularHours: $popularHours,
        totalQueries: 0,
        computedAt: '2026-01-01T00:00:00+00:00',
        topSearchQueries: [],
    );

    $mockRepo = Mockery::mock(QueryLogRepositoryInterface::class);
    $mockRepo->shouldReceive('getCachedStatistics')->once()->andReturnNull();
    $mockRepo->shouldReceive('computeStatistics')->once()->andReturn($stats);
    $mockRepo->shouldReceive('cacheStatistics')->once()->with($stats);

    $this->app->instance(QueryLogRepositoryInterface::class, $mockRepo);

    $response = $this->getJson('/api/statistics');

    $response
        ->assertOk()
        ->assertJsonPath('data.total_queries', 0)
        ->assertJsonStructure([
            'data' => [
                'top_queries',
                'top_search_queries',
                'popular_hours',
            ],
        ]);
});
