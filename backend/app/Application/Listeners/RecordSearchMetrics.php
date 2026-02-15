<?php

declare(strict_types=1);

namespace App\Application\Listeners;

use App\Application\Events\SearchQueryExecuted;
use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

final class RecordSearchMetrics implements ShouldQueue
{
    public function __construct(
        private readonly QueryLogRepositoryInterface $repository,
        private readonly AppLoggerInterface $logger,
    ) {}

    public function handle(SearchQueryExecuted $event): void
    {
        $this->repository->recordSearchQuery(
            $event->searchType,
            $event->query,
            $event->responseTimeMs,
            $event->resultCount,
        );
        $this->logger->info('Recorded search query metric', [
            'search_type' => $event->searchType,
            'query' => $event->query,
            'response_time_ms' => $event->responseTimeMs,
            'result_count' => $event->resultCount,
        ]);
    }
}
