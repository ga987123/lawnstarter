<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Events\FilmQueryExecuted;
use App\Application\Events\QueryExecuted;
use App\Application\Events\SearchQueryExecuted;
use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Swapi\Contracts\SwapiClientInterface;
use App\Domain\Swapi\DTOs\FilmDto;
use App\Domain\Swapi\DTOs\PaginatedResultDto;
use App\Domain\Swapi\DTOs\PersonDto;
use App\Domain\Swapi\DTOs\RelatedResourceDto;

final class StarwarsService
{
    public function __construct(
        private readonly SwapiClientInterface $client,
        private readonly AppLoggerInterface $logger,
    ) {}

    /**
     * Fetch a person and resolve related film URLs to {id, name}.
     *
     * @return array<string, mixed>
     */
    public function getPerson(int $id): array
    {
        $startTime = microtime(true);
        $this->logger->info('Fetching person from SWAPI', ['person_id' => $id]);

        $person = $this->client->fetchPerson($id);

        $responseTimeMs = (microtime(true) - $startTime) * 1000;

        QueryExecuted::dispatch($id, $responseTimeMs);

        $response = $person->toArray();
        $response['films'] = $this->resolveToArrays($person->films);

        return $response;
    }

    /**
     * Fetch a film and resolve related resource URLs to {id, name}.
     *
     * @return array<string, mixed>
     */
    public function getFilm(int $id): array
    {
        $startTime = microtime(true);
        $this->logger->info('Fetching film from SWAPI', ['film_id' => $id]);

        $film = $this->client->fetchFilm($id);

        $responseTimeMs = (microtime(true) - $startTime) * 1000;

        FilmQueryExecuted::dispatch($id, $responseTimeMs);

        $response = $film->toArray();
        $response['characters'] = $this->resolveToArrays($film->characters);

        return $response;
    }

    /**
     * Resolve SWAPI URLs and convert to serializable arrays.
     *
     * @param list<string> $urls
     * @return list<array{id: int, name: string}>
     */
    private function resolveToArrays(array $urls): array
    {
        return array_map(
            fn(RelatedResourceDto $dto) => $dto->toArray(),
            $this->client->resolveResourceNames($urls),
        );
    }

    /**
     * @param array<string, mixed> $queryParams
     * @return PaginatedResultDto<PersonDto>
     */
    public function searchPeople(array $queryParams): PaginatedResultDto
    {
        $startTime = microtime(true);

        $result = $this->client->searchPeople($queryParams);

        $responseTimeMs = (microtime(true) - $startTime) * 1000;
        $searchQuery = (string) ($queryParams['name'] ?? '');

        SearchQueryExecuted::dispatch('people', $searchQuery, $responseTimeMs, count($result->items));

        return $result;
    }

    /**
     * @param array<string, mixed> $queryParams
     * @return list<FilmDto>
     */
    public function searchFilms(array $queryParams): array
    {
        $startTime = microtime(true);

        $result = $this->client->searchFilms($queryParams);

        $responseTimeMs = (microtime(true) - $startTime) * 1000;
        $searchQuery = (string) ($queryParams['name'] ?? '');

        SearchQueryExecuted::dispatch('films', $searchQuery, $responseTimeMs, count($result));

        return $result;
    }
}
