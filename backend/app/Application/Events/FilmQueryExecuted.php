<?php

declare(strict_types=1);

namespace App\Application\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class FilmQueryExecuted
{
    use Dispatchable;

    public function __construct(
        public readonly int $filmId,
        public readonly float $responseTimeMs,
    ) {}
}
