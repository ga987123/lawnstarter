<?php

declare(strict_types=1);

namespace App\Domain\Swapi\Exceptions;

use RuntimeException;

final class SwapiNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Person with ID {$id} was not found in SWAPI.");
    }
}
