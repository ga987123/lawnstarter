<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Events\QueryExecuted;
use App\Domain\Swapi\Contracts\SwapiGatewayInterface;
use App\Domain\Swapi\DTOs\PersonDto;

final class SwapiService
{
    public function __construct(
        private readonly SwapiGatewayInterface $gateway,
    ) {}

    public function getPerson(int $id): PersonDto
    {
        $startTime = microtime(true);

        $person = $this->gateway->fetchPerson($id);

        $responseTimeMs = (microtime(true) - $startTime) * 1000;

        QueryExecuted::dispatch($id, $responseTimeMs);

        return $person;
    }

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API
     * @return list<\App\Domain\Swapi\DTOs\PersonDto>
     */
    public function searchPeople(array $queryParams): array
    {
        // if needed, should add cache with redis
        return $this->gateway->searchPeople($queryParams);
    }

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API
     * @return list<\App\Domain\Swapi\DTOs\FilmDto>
     */
    public function searchFilms(array $queryParams): array
    {
        // if needed, should add cache with redis
        return $this->gateway->searchFilms($queryParams);
    }
}
