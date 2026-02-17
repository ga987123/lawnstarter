<?php

declare(strict_types=1);

use App\Application\Services\StatisticsServiceInterface;
use App\Http\Controllers\StatisticsController;
use Tests\Mocks\StatisticsMocks;

it('returns JSON with statistics data from service', function (): void {
    $stats = StatisticsMocks::queryStatisticsDto([
        'totalQueries' => 42,
        'averageResponseTimeMs' => 100.5,
    ]);
    $service = Mockery::mock(StatisticsServiceInterface::class);
    $service->shouldReceive('getStatistics')->once()->andReturn($stats);

    $controller = new StatisticsController($service);
    $response = ($controller)();

    expect($response->getStatusCode())->toBe(200);
    $json = $response->getData(true);
    expect($json)->toHaveKey('data')
        ->and($json['data']['total_queries'])->toBe(42)
        ->and($json['data']['average_response_time_ms'])->toBe(100.5);
});

it('includes popular_hours and top_search_queries in response', function (): void {
    $stats = StatisticsMocks::queryStatisticsDto([
        'topSearchQueries' => [
            ['search_type' => 'people', 'query' => 'luke', 'count' => 5, 'percentage' => 50.0],
        ],
    ]);
    $service = Mockery::mock(StatisticsServiceInterface::class);
    $service->shouldReceive('getStatistics')->once()->andReturn($stats);

    $controller = new StatisticsController($service);
    $response = ($controller)();

    $json = $response->getData(true);
    expect($json['data'])->toHaveKeys(['popular_hours', 'top_search_queries', 'computed_at'])
        ->and($json['data']['top_search_queries'])->toHaveCount(1)
        ->and($json['data']['top_search_queries'][0]['query'])->toBe('luke');
});
