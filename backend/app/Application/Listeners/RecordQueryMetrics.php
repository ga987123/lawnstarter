<?php

declare(strict_types=1);

namespace App\Application\Listeners;

use App\Application\Events\QueryExecuted;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

final class RecordQueryMetrics implements ShouldQueue
{
    public function __construct(
        private readonly QueryLogRepositoryInterface $repository,
    ) {}

    public function handle(QueryExecuted $event): void
    {
        $this->repository->recordQuery(
            $event->personId,
            $event->responseTimeMs,
        );
    }
}
