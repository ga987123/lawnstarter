<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Swapi\DTOs\FilmDto;
use App\Domain\Swapi\DTOs\PaginatedResultDto;

interface StarwarsServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getPerson(int $id): array;

    /**
     * @return array<string, mixed>
     */
    public function getFilm(int $id): array;

    /**
     * @param array<string, mixed> $queryParams
     * @return PaginatedResultDto<\App\Domain\Swapi\DTOs\PersonDto>
     */
    public function searchPeople(array $queryParams): PaginatedResultDto;

    /**
     * @param array<string, mixed> $queryParams
     * @return list<FilmDto>
     */
    public function searchFilms(array $queryParams): array;
}
