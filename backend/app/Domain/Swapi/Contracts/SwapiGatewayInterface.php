<?php

declare(strict_types=1);

namespace App\Domain\Swapi\Contracts;

use App\Domain\Swapi\DTOs\FilmDto;
use App\Domain\Swapi\DTOs\PersonDto;

interface SwapiGatewayInterface
{
    public function fetchPerson(int $id): PersonDto;

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API (e.g., ['name' => 'r2'])
     * @return list<PersonDto>
     */
    public function searchPeople(array $queryParams): array;

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API (e.g., ['title' => 'A New Hope'])
     * @return list<FilmDto>
     */
    public function searchFilms(array $queryParams): array;
}
