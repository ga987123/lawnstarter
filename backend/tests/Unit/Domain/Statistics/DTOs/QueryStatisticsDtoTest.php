<?php

declare(strict_types=1);

use App\Domain\Statistics\DTOs\QueryStatisticsDto;
use Tests\Mocks\StatisticsMocks;

it('toArray returns expected keys and rounded average', function (): void {
    $dto = StatisticsMocks::queryStatisticsDto([
        'averageResponseTimeMs' => 123.456,
        'totalQueries' => 42,
        'computedAt' => '2026-02-01T12:00:00+00:00',
    ]);

    $arr = $dto->toArray();

    expect($arr)->toHaveKeys(['average_response_time_ms', 'popular_hours', 'total_queries', 'computed_at', 'top_search_queries'])
        ->and($arr['average_response_time_ms'])->toBe(123.46)
        ->and($arr['total_queries'])->toBe(42)
        ->and($arr['computed_at'])->toBe('2026-02-01T12:00:00+00:00')
        ->and($arr['popular_hours'])->toHaveCount(24);
});
