<?php

declare(strict_types=1);

use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use App\Domain\Statistics\DTOs\QueryStatisticsDto;

it('returns statistics from cache', function (): void {
    $mockRepo = Mockery::mock(QueryLogRepositoryInterface::class);
    $mockRepo->shouldReceive('getCachedStatistics')
        ->once()
        ->andReturn(new QueryStatisticsDto(
            topQueries: [
                ['person_id' => 1, 'count' => 10, 'percentage' => 50.0],
                ['person_id' => 2, 'count' => 5, 'percentage' => 25.0],
            ],
            averageResponseTimeMs: 150.5,
            popularHours: array_fill(0, 24, 0),
            totalQueries: 20,
            computedAt: '2026-01-01T00:00:00+00:00',
        ));

    $this->app->instance(QueryLogRepositoryInterface::class, $mockRepo);

    $response = $this->getJson('/api/statistics');

    $response
        ->assertOk()
        ->assertJsonPath('data.total_queries', 20)
        ->assertJsonPath('data.average_response_time_ms', 150.5)
        ->assertJsonStructure([
            'data' => [
                'top_queries',
                'average_response_time_ms',
                'popular_hours',
                'total_queries',
                'computed_at',
            ],
        ]);
});

it('computes statistics when cache is empty', function (): void {
    $stats = new QueryStatisticsDto(
        topQueries: [],
        averageResponseTimeMs: 0.0,
        popularHours: array_fill(0, 24, 0),
        totalQueries: 0,
        computedAt: '2026-01-01T00:00:00+00:00',
    );

    $mockRepo = Mockery::mock(QueryLogRepositoryInterface::class);
    $mockRepo->shouldReceive('getCachedStatistics')->once()->andReturnNull();
    $mockRepo->shouldReceive('computeStatistics')->once()->andReturn($stats);
    $mockRepo->shouldReceive('cacheStatistics')->once()->with($stats);

    $this->app->instance(QueryLogRepositoryInterface::class, $mockRepo);

    $response = $this->getJson('/api/statistics');

    $response
        ->assertOk()
        ->assertJsonPath('data.total_queries', 0);
});
