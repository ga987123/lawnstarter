<?php

declare(strict_types=1);

namespace App\Application\Jobs;

use App\Application\Services\StatisticsServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class RecomputeStatisticsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(StatisticsServiceInterface $service): void
    {
        /** @var \App\Application\Services\StatisticsService $service */
        $service->recompute();
    }
}
