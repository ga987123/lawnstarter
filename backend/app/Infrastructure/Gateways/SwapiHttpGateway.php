<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateways;

use App\Domain\Swapi\Contracts\SwapiGatewayInterface;
use App\Domain\Swapi\DTOs\FilmDto;
use App\Domain\Swapi\DTOs\PersonDto;
use App\Domain\Swapi\Exceptions\SwapiNotFoundException;
use App\Domain\Swapi\Exceptions\SwapiUnavailableException;

final class SwapiHttpGateway extends BaseHttpGateway implements SwapiGatewayInterface
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
        try {
            $response = $this->executeGet("/people/{$id}", [], notFoundStatus: 404);

            $body = $this->getJsonResponse($response);
            $properties = SwapiResponseWrapper::fromBody($body)->getProperties();

            return PersonDto::fromSwapiResponse($id, $properties);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), '404')) {
                throw new SwapiNotFoundException($id);
            }
            throw new SwapiUnavailableException('SWAPI request failed: ' . $e->getMessage(), $e);
        } catch (SwapiNotFoundException $e) {
            throw $e;
        } catch (SwapiUnavailableException $e) {
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API
     * @return list<PersonDto>
     */
    public function searchPeople(array $queryParams): array
    {
        try {
            $filteredParams = array_filter(
                $queryParams,
                fn($value) => $value !== null && $value !== '',
            );

            $apiParams = array_map(fn($value) => (string) $value, $filteredParams);

            $response = $this->executeGet('/people', $apiParams);

            $body = $this->getJsonResponse($response);
            $wrapper = SwapiResponseWrapper::fromBody($body);

            return PersonDto::fromResultItems($wrapper->getResults());
        } catch (\Throwable $e) {
            throw new SwapiUnavailableException(
                'SWAPI search request failed: ' . $e->getMessage(),
                $e,
            );
        }
    }

    /**
     * @param array<string, mixed> $queryParams Query parameters to pass to SWAPI API
     * @return list<FilmDto>
     */
    public function searchFilms(array $queryParams): array
    {
        try {
            $filteredParams = array_filter(
                $queryParams,
                fn($value) => $value !== null && $value !== '',
            );

            $apiParams = array_map(fn($value) => (string) $value, $filteredParams);

            $response = $this->executeGet('/films', $apiParams);

            $body = $this->getJsonResponse($response);
            $wrapper = SwapiResponseWrapper::fromBody($body);

            return FilmDto::fromResultItems($wrapper->getResults());
        } catch (\Throwable $e) {
            throw new SwapiUnavailableException(
                'SWAPI films search failed: ' . $e->getMessage(),
                $e,
            );
        }
    }
}
