<?php

declare(strict_types=1);

namespace App\Domain\Swapi\Exceptions;

use RuntimeException;

final class SwapiNotFoundException extends RuntimeException
{
    public function __construct(int $id, string $resource = 'Person')
    {
        parent::__construct("{$resource} with ID {$id} was not found in SWAPI.");
    }
}
