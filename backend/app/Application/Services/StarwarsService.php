<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Events\QueryExecuted;
use App\Application\Events\SearchQueryExecuted;
use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Swapi\Contracts\SwapiClientInterface;
use App\Domain\Swapi\DTOs\PaginatedResultDto;
use App\Domain\Swapi\DTOs\PersonDto;

final class StarwarsService
{
    public function __construct(
        private readonly SwapiClientInterface $client,
        private readonly AppLoggerInterface $logger,
    ) {}

    public function getPerson(int $id): PersonDto
    {
        $startTime = microtime(true);
        $this->logger->info('Fetching person from SWAPI', ['person_id' => $id]);

        $person = $this->client->fetchPerson($id);

        $responseTimeMs = (microtime(true) - $startTime) * 1000;

        QueryExecuted::dispatch($id, $responseTimeMs);

        return $person;
    }

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API
     * @return PaginatedResultDto<\App\Domain\Swapi\DTOs\PersonDto>
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
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API
     * @return PaginatedResultDto<\App\Domain\Swapi\DTOs\FilmDto>
     */
    public function searchFilms(array $queryParams): PaginatedResultDto
    {
        $startTime = microtime(true);

        $result = $this->client->searchFilms($queryParams);

        $responseTimeMs = (microtime(true) - $startTime) * 1000;
        $searchQuery = (string) ($queryParams['name'] ?? '');

        SearchQueryExecuted::dispatch('films', $searchQuery, $responseTimeMs, count($result->items));

        return $result;
    }
}
