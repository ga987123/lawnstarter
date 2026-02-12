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
     * @param  array<int, int>  $popularHours  Hour (0-23) => count
     */
    public function __construct(
        public array $topQueries,
        public float $averageResponseTimeMs,
        public array $popularHours,
        public int $totalQueries,
        public string $computedAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'top_queries' => $this->topQueries,
            'average_response_time_ms' => round($this->averageResponseTimeMs, 2),
            'popular_hours' => $this->popularHours,
            'total_queries' => $this->totalQueries,
            'computed_at' => $this->computedAt,
        ];
    }
}
