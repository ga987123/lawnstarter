<?php

declare(strict_types=1);

namespace App\Infrastructure\Clients\Swapi;

use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Swapi\Contracts\SwapiClientInterface;
use App\Domain\Swapi\DTOs\FilmDto;
use App\Domain\Swapi\DTOs\PaginatedResultDto;
use App\Domain\Swapi\DTOs\PersonDto;
use App\Domain\Swapi\DTOs\RelatedResourceDto;
use App\Domain\Swapi\Exceptions\SwapiNotFoundException;
use App\Domain\Swapi\Exceptions\SwapiUnavailableException;
use App\Infrastructure\Clients\BaseHttpClient;
use App\Infrastructure\Clients\CircuitBreaker;
use Illuminate\Support\Facades\Http;

final class SwapiHttpClients extends BaseHttpClient implements SwapiClientInterface
{
    private const CIRCUIT_BREAKER_KEY_PREFIX = 'swapi:circuit';

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

    protected function createCircuitBreaker(string $circuitKey): CircuitBreaker
    {
        return new CircuitBreaker(
            circuitKey: $circuitKey,
            failureThreshold: $this->circuitFailureThreshold,
            timeoutSeconds: $this->circuitTimeoutSeconds,
            halfOpenSuccessThreshold: $this->circuitHalfOpenSuccessThreshold,
            logger: $this->logger,
        );
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

    public function fetchFilm(int $id): FilmDto
    {
        $this->logger->info('SWAPI fetch film', ['film_id' => $id]);
        try {
            $response = $this->executeGet("/films/{$id}", [], notFoundStatus: 404);

            $body = $this->getJsonResponse($response);
            $properties = SwapiResponseWrapper::fromBody($body)->getProperties();

            return FilmDto::fromSwapiItem($id, $properties);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), '404')) {
                $this->logger->info('SWAPI film not found', ['film_id' => $id]);
                throw new SwapiNotFoundException($id, 'Film');
            }
            $this->logger->error('SWAPI fetch film failed', ['film_id' => $id, 'message' => $e->getMessage()]);
            throw new SwapiUnavailableException('SWAPI request failed: ' . $e->getMessage(), $e);
        } catch (SwapiNotFoundException $e) {
            throw $e;
        } catch (SwapiUnavailableException $e) {
            $this->logger->error('SWAPI unavailable', ['film_id' => $id, 'message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $queryParams
     * @return PaginatedResultDto<PersonDto>
     */
    public function searchPeople(array $queryParams): PaginatedResultDto
    {
        $this->logger->info('SWAPI search people', ['query_params' => $queryParams]);
        try {
            $page = (int) ($queryParams['page'] ?? 1);
            $apiParams = $this->buildApiParams($queryParams);

            $response = $this->executeGet('/people', $apiParams);
            $body = $this->getJsonResponse($response);
            $wrapper = SwapiResponseWrapper::fromBody($body);

            return new PaginatedResultDto(
                items: PersonDto::fromResultItems($wrapper->getResults()),
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
     * SWAPI's /films endpoint does not support pagination; all films are returned at once.
     *
     * @param array<string, mixed> $queryParams
     * @return list<FilmDto>
     */
    public function searchFilms(array $queryParams): array
    {
        $this->logger->info('SWAPI search films', ['query_params' => $queryParams]);
        try {
            $apiParams = $this->buildApiParams($queryParams);

            $response = $this->executeGet('/films', $apiParams);
            $body = $this->getJsonResponse($response);
            $wrapper = SwapiResponseWrapper::fromBody($body);

            return FilmDto::fromResultItems($wrapper->getResults());
        } catch (\Throwable $e) {
            $this->logger->error('SWAPI search films failed', ['message' => $e->getMessage()]);
            throw new SwapiUnavailableException(
                'SWAPI films search failed: ' . $e->getMessage(),
                $e,
            );
        }
    }

    /**
     * Resolve a list of SWAPI resource URLs into {id, name} pairs using concurrent requests.
     *
     * @param list<string> $urls Full SWAPI URLs
     * @return list<RelatedResourceDto>
     */
    public function resolveResourceNames(array $urls): array
    {
        if ($urls === []) {
            return [];
        }

        $this->logger->info('SWAPI resolving resource names', ['count' => count($urls)]);

        $responses = Http::pool(function ($pool) use ($urls) {
            foreach ($urls as $url) {
                $pool->as($url)->timeout($this->timeout)->acceptJson()->get($url);
            }
        });

        $resolved = [];
        foreach ($urls as $url) {
            $id = $this->extractIdFromUrl($url);
            $isFilm = str_contains($url, '/films/');

            try {
                $response = $responses[$url];

                if ($response->successful()) {
                    $body = $response->json();
                    $wrapper = SwapiResponseWrapper::fromBody(is_array($body) ? $body : []);
                    $properties = $wrapper->getProperties();
                    $name = $isFilm
                        ? (string) ($properties['title'] ?? 'Unknown')
                        : (string) ($properties['name'] ?? 'Unknown');

                    $resolved[] = new RelatedResourceDto($id, $name);
                } else {
                    $this->logger->warning('SWAPI resource resolution failed', ['url' => $url, 'status' => $response->status()]);
                    $resolved[] = new RelatedResourceDto($id, 'Unknown');
                }
            } catch (\Throwable $e) {
                $this->logger->warning('SWAPI resource resolution error', ['url' => $url, 'message' => $e->getMessage()]);
                $resolved[] = new RelatedResourceDto($id, 'Unknown');
            }
        }

        return $resolved;
    }

    /**
     * Extract the numeric ID from a SWAPI URL (last path segment).
     */
    private function extractIdFromUrl(string $url): int
    {
        $path = rtrim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        return (int) basename($path);
    }

    /**
     * Filter and stringify query parameters for the SWAPI request.
     *
     * @param array<string, mixed> $queryParams
     * @return array<string, string>
     */
    private function buildApiParams(array $queryParams): array
    {
        $filtered = array_filter(
            $queryParams,
            fn($value) => $value !== null && $value !== '',
        );

        return array_map(fn($value) => (string) $value, $filtered);
    }
}
