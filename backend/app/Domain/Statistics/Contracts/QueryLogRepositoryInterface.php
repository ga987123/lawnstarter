<?php

declare(strict_types=1);

namespace App\Domain\Statistics\Contracts;

use App\Domain\Statistics\DTOs\QueryStatisticsDto;

interface QueryLogRepositoryInterface
{
    public function recordQuery(int $personId, float $responseTimeMs): void;

    public function recordFilmQuery(int $filmId, float $responseTimeMs): void;

    public function recordSearchQuery(string $searchType, string $query, float $responseTimeMs, int $resultCount): void;

    public function computeStatistics(): QueryStatisticsDto;

    public function getCachedStatistics(): ?QueryStatisticsDto;

    public function cacheStatistics(QueryStatisticsDto $statistics): void;
}
