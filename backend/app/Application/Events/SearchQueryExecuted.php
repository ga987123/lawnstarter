<?php

declare(strict_types=1);

namespace App\Application\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class SearchQueryExecuted
{
    use Dispatchable;

    public function __construct(
        public readonly string $searchType,
        public readonly string $query,
        public readonly float $responseTimeMs,
        public readonly int $resultCount,
    ) {}
}
