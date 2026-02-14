<?php

declare(strict_types=1);

namespace App\Domain\Swapi\Contracts;

use App\Domain\Swapi\DTOs\FilmDto;
use App\Domain\Swapi\DTOs\PaginatedResultDto;
use App\Domain\Swapi\DTOs\PersonDto;
use App\Domain\Swapi\DTOs\RelatedResourceDto;

interface SwapiClientInterface
{
    public function fetchPerson(int $id): PersonDto;

    public function fetchFilm(int $id): FilmDto;

    /**
     * @param array<string, mixed> $queryParams Query parameters (name, page, limit)
     * @return PaginatedResultDto<PersonDto>
     */
    public function searchPeople(array $queryParams): PaginatedResultDto;

    /**
     * @param array<string, mixed> $queryParams Query parameters (name)
     * @return list<FilmDto>
     */
    public function searchFilms(array $queryParams): array;

    /**
     * Resolve a list of SWAPI resource URLs into {id, name} pairs.
     * Makes concurrent HTTP requests. On individual failure, returns {id, name: "Unknown"}.
     *
     * @param list<string> $urls Full SWAPI URLs (e.g., "https://www.swapi.tech/api/people/1")
     * @return list<RelatedResourceDto>
     */
    public function resolveResourceNames(array $urls): array;
}
