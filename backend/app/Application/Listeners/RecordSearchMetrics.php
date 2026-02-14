<?php

declare(strict_types=1);

namespace App\Application\Listeners;

use App\Application\Events\SearchQueryExecuted;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

final class RecordSearchMetrics implements ShouldQueue
{
    public function __construct(
        private readonly QueryLogRepositoryInterface $repository,
    ) {}

    public function handle(SearchQueryExecuted $event): void
    {
        $this->repository->recordSearchQuery(
            $event->searchType,
            $event->query,
            $event->responseTimeMs,
            $event->resultCount,
        );
    }
}
