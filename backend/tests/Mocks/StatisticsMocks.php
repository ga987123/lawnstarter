<?php

declare(strict_types=1);

namespace Tests\Mocks;

use App\Domain\Statistics\DTOs\QueryStatisticsDto;

final class StatisticsMocks
{
    /**
     * @param array<string, mixed> $overrides
     */
    public static function queryStatisticsDto(array $overrides = []): QueryStatisticsDto
    {
        /** @var list<array{hour: int, total_count: int}> $popularHours */
        $popularHours = array_values(array_map(
            fn(int $h) => ['hour' => $h, 'total_count' => 0],
            range(0, 23),
        ));

        $defaults = [
            'averageResponseTimeMs' => 0.0,
            'popularHours' => $popularHours,
            'totalQueries' => 0,
            'computedAt' => '2026-01-01T00:00:00+00:00',
            'topSearchQueries' => [],
        ];
        $merged = array_merge($defaults, $overrides);

        /** @var list<array{hour: int, total_count: int}> $popularHoursMerged */
        $popularHoursMerged = is_array($merged['popularHours'] ?? null) ? array_values($merged['popularHours']) : $popularHours;
        /** @var list<array{search_type: string, query: string, count: int, percentage: float}> $topSearchMerged */
        $topSearchMerged = is_array($merged['topSearchQueries'] ?? null) ? array_values($merged['topSearchQueries']) : [];

        return new QueryStatisticsDto(
            averageResponseTimeMs: (float) ($merged['averageResponseTimeMs'] ?? 0.0),
            popularHours: $popularHoursMerged,
            totalQueries: (int) ($merged['totalQueries'] ?? 0),
            computedAt: (string) ($merged['computedAt'] ?? '2026-01-01T00:00:00+00:00'),
            topSearchQueries: $topSearchMerged,
        );
    }
}
