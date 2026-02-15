<?php

declare(strict_types=1);

namespace App\Domain\Statistics\DTOs;

/**
 * Represents the computed query statistics.
 */
final readonly class QueryStatisticsDto
{
    /**
     * @param  array<int, array{person_id: int, count: int, percentage: float}>  $topQueries
     * @param  list<array{hour: int, total_count: int}>  $popularHours  One entry per hour 0-23
     * @param  list<array{search_type: string, query: string, count: int, percentage: float}>  $topSearchQueries
     */
    public function __construct(
        public float $averageResponseTimeMs,
        public array $popularHours,
        public int $totalQueries,
        public string $computedAt,
        public array $topSearchQueries = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'average_response_time_ms' => round($this->averageResponseTimeMs, 2),
            'popular_hours' => $this->popularHours,
            'total_queries' => $this->totalQueries,
            'computed_at' => $this->computedAt,
            'top_search_queries' => $this->topSearchQueries,
        ];
    }
}
