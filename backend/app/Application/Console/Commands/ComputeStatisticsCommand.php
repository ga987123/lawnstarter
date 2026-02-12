<?php

declare(strict_types=1);

namespace App\Application\Console\Commands;

use App\Application\Jobs\RecomputeStatisticsJob;
use Illuminate\Console\Command;

final class ComputeStatisticsCommand extends Command
{
    protected $signature = 'statistics:compute';

    protected $description = 'Dispatch a job to recompute query statistics';

    public function handle(): int
    {
        RecomputeStatisticsJob::dispatch();

        $this->info('Statistics recomputation job dispatched.');

        return self::SUCCESS;
    }
}
