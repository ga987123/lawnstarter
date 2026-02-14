<?php

declare(strict_types=1);

namespace App\Infrastructure\Clients\Swapi;

use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Swapi\Contracts\SwapiClientInterface;
use App\Domain\Swapi\DTOs\FilmDto;
use App\Domain\Swapi\DTOs\PaginatedResultDto;
use App\Domain\Swapi\DTOs\PersonDto;
use App\Domain\Swapi\Exceptions\SwapiNotFoundException;
use App\Domain\Swapi\Exceptions\SwapiUnavailableException;
use App\Infrastructure\Clients\BaseHttpClient;

final class SwapiHttpClients extends BaseHttpClient implements SwapiClientInterface
{
    private const CIRCUIT_BREAKER_KEY_PREFIX = 'swapi:circuit';
    private const DEFAULT_PAGE_SIZE = 10;

    public function __construct(
        string $baseUrl,
        int $timeout,
        int $retryTimes,
        int $retrySleep,
        int $circuitFailureThreshold = 5,
        int $circuitTimeoutSeconds = 60,
        int $circuitHalfOpenSuccessThreshold = 2,
        private readonly AppLoggerInterface $logger,
    ) {
        parent::__construct(
            baseUrl: $baseUrl,
            timeout: $timeout,
            retryTimes: $retryTimes,
            retrySleep: $retrySleep,
            circuitFailureThreshold: $circuitFailureThreshold,
            circuitTimeoutSeconds: $circuitTimeoutSeconds,
            circuitHalfOpenSuccessThreshold: $circuitHalfOpenSuccessThreshold,
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getCircuitBreakerKey(string $endpoint, array $params = []): string
    {
        $normalizedEndpoint = trim($endpoint, '/');
        $normalizedEndpoint = str_replace('/', ':', $normalizedEndpoint);

        return self::CIRCUIT_BREAKER_KEY_PREFIX . ':' . $normalizedEndpoint;
    }

    public function fetchPerson(int $id): PersonDto
    {
        $this->logger->info('SWAPI fetch person', ['person_id' => $id]);
        try {
            $response = $this->executeGet("/people/{$id}", [], notFoundStatus: 404);

            $body = $this->getJsonResponse($response);
            $properties = SwapiResponseWrapper::fromBody($body)->getProperties();

            return PersonDto::fromSwapiResponse($id, $properties);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), '404')) {
                $this->logger->info('SWAPI person not found', ['person_id' => $id]);
                throw new SwapiNotFoundException($id);
            }
            $this->logger->error('SWAPI fetch person failed', ['person_id' => $id, 'message' => $e->getMessage()]);
            throw new SwapiUnavailableException('SWAPI request failed: ' . $e->getMessage(), $e);
        } catch (SwapiNotFoundException $e) {
            throw $e;
        } catch (SwapiUnavailableException $e) {
            $this->logger->error('SWAPI unavailable', ['person_id' => $id, 'message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API
     * @return PaginatedResultDto<PersonDto>
     */
    public function searchPeople(array $queryParams): PaginatedResultDto
    {
        $this->logger->info('SWAPI search people', ['query_params' => $queryParams]);
        try {
            $page = (int) ($queryParams['page'] ?? 1);
            $limit = self::DEFAULT_PAGE_SIZE;
            $isNameSearch = isset($queryParams['name']) && $queryParams['name'] !== '';

            $filteredParams = array_filter(
                $queryParams,
                fn($value) => $value !== null && $value !== '',
            );
            $apiParams = array_map(fn($value) => (string) $value, $filteredParams);

            // SWAPI ignores `page` for name searches and returns all results at once,
            // so we strip page/limit and paginate server-side.
            if ($isNameSearch) {
                unset($apiParams['page'], $apiParams['limit']);
            }

            $response = $this->executeGet('/people', $apiParams);
            $body = $this->getJsonResponse($response);
            $wrapper = SwapiResponseWrapper::fromBody($body);

            $allItems = PersonDto::fromResultItems($wrapper->getResults());

            if ($isNameSearch) {
                return $this->paginateLocally($allItems, $page, $limit);
            }

            return new PaginatedResultDto(
                items: $allItems,
                currentPage: $page,
                totalPages: $wrapper->getTotalPages(),
                totalRecords: $wrapper->getTotalRecords(),
                hasNextPage: $wrapper->hasNextPage(),
            );
        } catch (\Throwable $e) {
            $this->logger->error('SWAPI search people failed', ['message' => $e->getMessage()]);
            throw new SwapiUnavailableException(
                'SWAPI search request failed: ' . $e->getMessage(),
                $e,
            );
        }
    }

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API
     * @return PaginatedResultDto<FilmDto>
     */
    public function searchFilms(array $queryParams): PaginatedResultDto
    {
        $this->logger->info('SWAPI search films', ['query_params' => $queryParams]);
        try {
            $page = (int) ($queryParams['page'] ?? 1);
            $limit = self::DEFAULT_PAGE_SIZE;
            $isNameSearch = isset($queryParams['name']) && $queryParams['name'] !== '';

            $filteredParams = array_filter(
                $queryParams,
                fn($value) => $value !== null && $value !== '',
            );
            $apiParams = array_map(fn($value) => (string) $value, $filteredParams);

            if ($isNameSearch) {
                unset($apiParams['page'], $apiParams['limit']);
            }

            $response = $this->executeGet('/films', $apiParams);
            $body = $this->getJsonResponse($response);
            $wrapper = SwapiResponseWrapper::fromBody($body);

            $allItems = FilmDto::fromResultItems($wrapper->getResults());

            if ($isNameSearch) {
                return $this->paginateLocally($allItems, $page, $limit);
            }

            return new PaginatedResultDto(
                items: $allItems,
                currentPage: $page,
                totalPages: $wrapper->getTotalPages(),
                totalRecords: $wrapper->getTotalRecords(),
                hasNextPage: $wrapper->hasNextPage(),
            );
        } catch (\Throwable $e) {
            $this->logger->error('SWAPI search films failed', ['message' => $e->getMessage()]);
            throw new SwapiUnavailableException(
                'SWAPI films search failed: ' . $e->getMessage(),
                $e,
            );
        }
    }

    /**
     * Paginate an array of items server-side.
     * Used when SWAPI returns all results at once (e.g. name search).
     *
     * @template T
     * @param list<T> $allItems
     * @return PaginatedResultDto<T>
     */
    private function paginateLocally(array $allItems, int $page, int $limit): PaginatedResultDto
    {
        $totalRecords = count($allItems);
        $totalPages = max(1, (int) ceil($totalRecords / $limit));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $limit;
        $pageItems = array_values(array_slice($allItems, $offset, $limit));

        return new PaginatedResultDto(
            items: $pageItems,
            currentPage: $page,
            totalPages: $totalPages,
            totalRecords: $totalRecords,
            hasNextPage: $page < $totalPages,
        );
    }
}
