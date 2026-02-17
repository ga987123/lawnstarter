<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Statistics\DTOs\QueryStatisticsDto;

interface StatisticsServiceInterface
{
    public function getStatistics(): QueryStatisticsDto;

    public function recompute(): QueryStatisticsDto;
}
