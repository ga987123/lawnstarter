<?php

declare(strict_types=1);

namespace App\Application\Listeners;

use App\Application\Events\FilmQueryExecuted;
use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

final class RecordFilmQueryMetrics implements ShouldQueue
{
    public function __construct(
        private readonly QueryLogRepositoryInterface $repository,
        private readonly AppLoggerInterface $logger,
    ) {}

    public function handle(FilmQueryExecuted $event): void
    {
        $this->repository->recordFilmQuery(
            $event->filmId,
            $event->responseTimeMs,
        );
        $this->logger->info('Recorded film query metric', [
            'film_id' => $event->filmId,
            'response_time_ms' => $event->responseTimeMs,
        ]);
    }
}
