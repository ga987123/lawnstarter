<?php

declare(strict_types=1);

namespace App\Domain\Swapi\Contracts;

use App\Domain\Swapi\DTOs\PaginatedResultDto;
use App\Domain\Swapi\DTOs\PersonDto;

interface SwapiClientInterface
{
    public function fetchPerson(int $id): PersonDto;

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API (e.g., ['name' => 'r2', 'page' => 1])
     * @return PaginatedResultDto<PersonDto>
     */
    public function searchPeople(array $queryParams): PaginatedResultDto;

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API (e.g., ['title' => 'A New Hope', 'page' => 1])
     * @return PaginatedResultDto<\App\Domain\Swapi\DTOs\FilmDto>
     */
    public function searchFilms(array $queryParams): PaginatedResultDto;
}
