<?php

declare(strict_types=1);

namespace App\Application\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class QueryExecuted
{
    use Dispatchable;

    public function __construct(
        public readonly int $personId,
        public readonly float $responseTimeMs,
    ) {}
}
