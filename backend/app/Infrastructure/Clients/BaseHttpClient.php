<?php

declare(strict_types=1);

namespace App\Infrastructure\Clients;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class BaseHttpClient
{
    public function __construct(
        protected readonly string $baseUrl,
        protected readonly int $timeout,
        protected readonly int $retryTimes,
        protected readonly int $retrySleep,
        protected readonly int $circuitFailureThreshold = 5,
        protected readonly int $circuitTimeoutSeconds = 60,
        protected readonly int $circuitHalfOpenSuccessThreshold = 2,
    ) {}

    /**
     * Execute a GET request with retry, timeout, and circuit breaker protection.
     * Builds the circuit key and request callback internally.
     *
     * @param string $path Request path (e.g., '/people', '/people/1', '/films')
     * @param array<string, mixed> $queryParams Query parameters for the request
     * @param int|null $notFoundStatus HTTP status code that indicates not found (e.g., 404)
     * @return Response
     * @throws \Throwable
     */
    protected function executeGet(
        string $path,
        array $queryParams = [],
        ?int $notFoundStatus = null,
    ): Response {
        $circuitKey = $this->getCircuitBreakerKey($path, $queryParams);

        return $this->executeRequest(
            fn (PendingRequest $client) => $client->get($path, $queryParams),
            $circuitKey,
            $notFoundStatus,
        );
    }

    /**
     * Return a unique circuit breaker key for the given endpoint and params.
     * Override in child classes to define gateway-specific key prefix/format.
     *
     * @param string $endpoint Request path (e.g., '/people', '/films')
     * @param array<string, mixed> $params Query or path parameters
     * @return string Circuit breaker key
     */
    abstract protected function getCircuitBreakerKey(string $endpoint, array $params = []): string;

    /**
     * Execute an HTTP request with retry, timeout, and circuit breaker protection.
     *
     * @param callable(PendingRequest): Response $requestCallback
     * @param string $circuitKey Unique key for circuit breaker
     * @param int|null $notFoundStatus HTTP status code that indicates not found (e.g., 404)
     * @return Response
     * @throws \Throwable
     */
    protected function executeRequest(
        callable $requestCallback,
        string $circuitKey,
        ?int $notFoundStatus = null,
    ): Response {
        $circuitBreaker = $this->createCircuitBreaker($circuitKey);
        $circuitBreaker->check();

        try {
            $response = $this->makeRequestWithRetry($requestCallback);

            $this->validateResponse($response, $notFoundStatus);

            $circuitBreaker->recordSuccess();

            return $response;
        } catch (\Throwable $e) {
            if ($this->shouldRecordFailure($e, $notFoundStatus)) {
                $circuitBreaker->recordFailure();
            }

            throw $this->handleException($e, $notFoundStatus);
        }
    }

    /**
     * Determine if a failure should be recorded in the circuit breaker.
     * Only records failures for server errors (>= 500) or invalid status codes (<= 100).
     * Client errors (4xx) do not trigger circuit breaker failures.
     *
     * @param \Throwable $e The exception that occurred
     * @param int|null $notFoundStatus HTTP status code that indicates not found (e.g., 404)
     * @return bool True if failure should be recorded, false otherwise
     */
    protected function shouldRecordFailure(\Throwable $e, ?int $notFoundStatus = null): bool
    {
        if ($e instanceof ConnectionException) {
            return true;
        }

        if ($e instanceof RequestException && $e->response !== null) {
            $status = $e->response->status();
            return $status >= 500 || $status <= 100;
        }

        if ($e instanceof \RuntimeException) {
            if ($notFoundStatus !== null && (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), (string) $notFoundStatus))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Handle exceptions thrown during HTTP requests.
     * Override this method in child classes to customize exception handling.
     *
     * @param \Throwable $e The caught exception
     * @param int|null $notFoundStatus HTTP status code that indicates not found
     * @return \Throwable The exception to throw (may be transformed)
     */
    protected function handleException(\Throwable $e, ?int $notFoundStatus = null): \Throwable
    {
        if ($e instanceof \RuntimeException) {
            if ($notFoundStatus !== null && (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), (string) $notFoundStatus))) {
                return $e;
            }
            return $this->createUnavailableException('Request failed: ' . $e->getMessage(), $e);
        }

        if ($e instanceof ConnectionException) {
            return $this->createUnavailableException('Could not connect to service.', $e);
        }

        return $this->createUnavailableException('Request failed: ' . $e->getMessage(), $e);
    }

    /**
     * Create an unavailable exception.
     * Override this method in child classes to return domain-specific exceptions.
     *
     * @param string $message The error message
     * @param \Throwable|null $previous The previous exception
     * @return \Throwable
     */
    protected function createUnavailableException(string $message, ?\Throwable $previous = null): \Throwable
    {
        return new \RuntimeException($message, 0, $previous);
    }

    /**
     * Create a circuit breaker instance for the given key.
     */
    protected function createCircuitBreaker(string $circuitKey): CircuitBreaker
    {
        return new CircuitBreaker(
            circuitKey: $circuitKey,
            failureThreshold: $this->circuitFailureThreshold,
            timeoutSeconds: $this->circuitTimeoutSeconds,
            halfOpenSuccessThreshold: $this->circuitHalfOpenSuccessThreshold,
        );
    }

    /**
     * Validate HTTP response and throw appropriate exceptions.
     *
     * @param Response $response
     * @param int|null $notFoundStatus HTTP status code that indicates not found (e.g., 404)
     * @throws \RuntimeException
     */
    protected function validateResponse(Response $response, ?int $notFoundStatus = null): void
    {
        if ($notFoundStatus !== null && $response->status() === $notFoundStatus) {
            throw new \RuntimeException("Resource not found (HTTP {$notFoundStatus})");
        }

        if ($response->failed()) {
            throw new \RuntimeException(
                "HTTP request failed with status {$response->status()}"
            );
        }
    }

    /**
     * Get JSON response body as array.
     *
     * @param Response $response
     * @return array<string, mixed>
     */
    protected function getJsonResponse(Response $response): array
    {
        $body = $response->json();

        if (!is_array($body)) {
            throw new \RuntimeException('Invalid JSON response format');
        }

        return $body;
    }

    /**
     * Create a configured HTTP client instance.
     */
    protected function createHttpClient(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson();
    }

    /**
     * Create a configured HTTP client instance without base URL.
     * Useful for requests with full URLs.
     */
    protected function createHttpClientWithoutBaseUrl(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->acceptJson();
    }

    /**
     * Make HTTP request with retry logic.
     *
     * @param callable(PendingRequest): Response $requestCallback
     * @return Response
     * @throws \Throwable
     */
    private function makeRequestWithRetry(callable $requestCallback): Response
    {
        return $this->makeRequestWithRetryInternal($requestCallback, fn() => $this->createHttpClient());
    }

    /**
     * Internal method to make HTTP request with retry logic.
     *
     * @param callable(PendingRequest): Response $requestCallback
     * @param callable(): PendingRequest $clientFactory
     * @return Response
     * @throws \Throwable
     */
    private function makeRequestWithRetryInternal(
        callable $requestCallback,
        callable $clientFactory,
    ): Response {
        $attempt = 0;
        $lastException = null;

        while ($attempt <= $this->retryTimes) {
            try {
                $client = $clientFactory();

                if ($attempt > 0) {
                    usleep($this->retrySleep * 1000); // Convert to microseconds
                }

                $response = $requestCallback($client);

                // Check if response indicates a retryable error
                if ($this->shouldRetry($response, $attempt)) {
                    $attempt++;
                    continue;
                }

                return $response;
            } catch (ConnectionException $e) {
                $lastException = $e;
                if ($attempt < $this->retryTimes) {
                    $attempt++;
                    continue;
                }
                throw $e;
            } catch (RequestException $e) {
                $lastException = $e;
                if ($this->isRetryableRequestException($e) && $attempt < $this->retryTimes) {
                    $attempt++;
                    continue;
                }
                throw $e;
            } catch (\Throwable $e) {
                throw $e;
            }
        }

        throw $lastException ?? new \RuntimeException('Request failed after retries');
    }

    /**
     * Determine if a response should trigger a retry.
     */
    private function shouldRetry(Response $response, int $attempt): bool
    {
        if ($attempt >= $this->retryTimes) {
            return false;
        }

        return $response->serverError();
    }

    /**
     * Determine if a RequestException is retryable.
     */
    private function isRetryableRequestException(RequestException $e): bool
    {
        if ($e->response === null) {
            return false;
        }

        return $e->response->serverError();
    }
}
