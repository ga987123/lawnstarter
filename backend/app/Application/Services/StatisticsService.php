<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use App\Domain\Statistics\DTOs\QueryStatisticsDto;

final class StatisticsService implements StatisticsServiceInterface
{
    public function __construct(
        private readonly QueryLogRepositoryInterface $repository,
    ) {}

    public function getStatistics(): QueryStatisticsDto
    {
        $cached = $this->repository->getCachedStatistics();

        if ($cached !== null) {
            return $cached;
        }

        // If no cached stats exist yet, compute and cache them now
        return $this->recompute();
    }

    public function recompute(): QueryStatisticsDto
    {
        $statistics = $this->repository->computeStatistics();
        $this->repository->cacheStatistics($statistics);

        return $statistics;
    }
}
