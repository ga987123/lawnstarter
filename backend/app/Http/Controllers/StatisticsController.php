<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Services\StatisticsServiceInterface;
use Illuminate\Http\JsonResponse;

final class StatisticsController
{
    public function __construct(
        private readonly StatisticsServiceInterface $statisticsService,
    ) {}

    public function __invoke(): JsonResponse
    {
        $statistics = $this->statisticsService->getStatistics();

        return response()->json([
            'data' => $statistics->toArray(),
        ]);
    }
}
