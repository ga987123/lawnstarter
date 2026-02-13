<?php

declare(strict_types=1);

namespace App\Domain\Swapi\Exceptions;

use RuntimeException;

final class SwapiUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'SWAPI service is currently unavailable.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
