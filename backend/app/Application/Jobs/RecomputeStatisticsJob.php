<?php

declare(strict_types=1);

namespace App\Application\Jobs;

use App\Application\Services\StatisticsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class RecomputeStatisticsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(StatisticsService $service): void
    {
        $service->recompute();
    }
}
