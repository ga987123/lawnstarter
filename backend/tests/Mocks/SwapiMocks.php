<?php

declare(strict_types=1);

namespace Tests\Mocks;

use App\Domain\Swapi\DTOs\FilmDto;
use App\Domain\Swapi\DTOs\PaginatedResultDto;
use App\Domain\Swapi\DTOs\PersonDto;
use App\Domain\Swapi\DTOs\RelatedResourceDto;

final class SwapiMocks
{
    /**
     * @param array<string, mixed> $overrides
     */
    public static function personDto(int $id = 1, array $overrides = []): PersonDto
    {
        $defaults = [
            'id' => $id,
            'name' => 'Luke Skywalker',
            'height' => '172',
            'mass' => '77',
            'birthYear' => '19BBY',
            'gender' => 'male',
            'skinColor' => 'fair',
            'hairColor' => 'blond',
            'eyeColor' => 'blue',
            'films' => [],
        ];
        $merged = array_merge($defaults, $overrides);

        /** @var list<string> $films */
        $films = is_array($merged['films'] ?? null) ? array_values($merged['films']) : [];

        return new PersonDto(
            id: (int) $merged['id'],
            name: (string) $merged['name'],
            height: (string) $merged['height'],
            mass: (string) $merged['mass'],
            birthYear: (string) $merged['birthYear'],
            gender: (string) $merged['gender'],
            skinColor: (string) $merged['skinColor'],
            hairColor: (string) $merged['hairColor'],
            eyeColor: (string) $merged['eyeColor'],
            films: $films,
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public static function filmDto(int $id = 1, array $overrides = []): FilmDto
    {
        $defaults = [
            'id' => $id,
            'title' => 'A New Hope',
            'episodeId' => 4,
            'director' => 'George Lucas',
            'producer' => 'Gary Kurtz',
            'releaseDate' => '1977-05-25',
            'openingCrawl' => 'It is a period of civil war.',
            'characters' => [],
        ];
        $merged = array_merge($defaults, $overrides);

        /** @var list<string> $characters */
        $characters = is_array($merged['characters'] ?? null) ? array_values($merged['characters']) : [];

        return new FilmDto(
            id: (int) $merged['id'],
            title: (string) $merged['title'],
            episodeId: (int) $merged['episodeId'],
            director: (string) $merged['director'],
            producer: (string) $merged['producer'],
            releaseDate: (string) $merged['releaseDate'],
            openingCrawl: (string) $merged['openingCrawl'],
            characters: $characters,
        );
    }

    public static function relatedResourceDto(int $id, string $name): RelatedResourceDto
    {
        return new RelatedResourceDto(id: $id, name: $name);
    }

    /**
     * @param int $count Number of PersonDto items
     * @return PaginatedResultDto<PersonDto>
     */
    public static function paginatedPeople(int $count = 1): PaginatedResultDto
    {
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $items[] = self::personDto($i);
        }

        return new PaginatedResultDto(
            items: $items,
            currentPage: 1,
            totalPages: 1,
            totalRecords: $count,
            hasNextPage: false,
        );
    }

    /**
     * @return list<FilmDto>
     */
    public static function filmDtoList(int $count = 1): array
    {
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $items[] = self::filmDto($i);
        }
        return $items;
    }
}
