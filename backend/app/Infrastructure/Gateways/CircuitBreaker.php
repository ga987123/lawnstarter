<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateways;

use Illuminate\Support\Facades\Redis;

final class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';
    private const STATE_OPEN = 'open';
    private const STATE_HALF_OPEN = 'half_open';

    public function __construct(
        private readonly string $circuitKey,
        private readonly int $failureThreshold = 5,
        private readonly int $timeoutSeconds = 60,
        private readonly int $halfOpenSuccessThreshold = 2,
    ) {}

    /**
     * Check if circuit breaker allows the request.
     * Throws exception if circuit is open.
     *
     * @throws \RuntimeException
     */
    public function check(): void
    {
        $state = $this->getState();

        if ($state === self::STATE_OPEN) {
            $lastFailureTime = $this->getLastFailureTime();
            $timeSinceLastFailure = time() - $lastFailureTime;

            if ($timeSinceLastFailure >= $this->timeoutSeconds) {
                // Transition to half-open
                $this->setState(self::STATE_HALF_OPEN);
                $this->resetHalfOpenSuccessCount();
                return;
            }

            throw new \RuntimeException(
                'Circuit breaker is OPEN. Service is unavailable. ' .
                "Last failure: {$timeSinceLastFailure}s ago. " .
                "Retry after {$this->timeoutSeconds}s."
            );
        }

        if ($state === self::STATE_HALF_OPEN) {
            $halfOpenSuccessCount = $this->getHalfOpenSuccessCount();

            if ($halfOpenSuccessCount >= $this->halfOpenSuccessThreshold) {
                // Transition to closed
                $this->setState(self::STATE_CLOSED);
                $this->resetFailureCount();
                $this->resetHalfOpenSuccessCount();
            }
        }
    }

    /**
     * Record a successful request.
     */
    public function recordSuccess(): void
    {
        $state = $this->getState();

        if ($state === self::STATE_HALF_OPEN) {
            $this->incrementHalfOpenSuccessCount();
        } elseif ($state === self::STATE_CLOSED) {
            // Reset failure count on success in closed state
            $this->resetFailureCount();
        }

        // Always increment success count
        $this->incrementSuccessCount();
    }

    /**
     * Record a failed request.
     */
    public function recordFailure(): void
    {
        $state = $this->getState();

        if ($state === self::STATE_HALF_OPEN) {
            // Failure in half-open state, open the circuit
            $this->setState(self::STATE_OPEN);
            $this->setLastFailureTime();
            $this->resetHalfOpenSuccessCount();
            $this->incrementFailureCount();
            return;
        }

        // Increment failure count
        $failureCount = $this->incrementFailureCount();
        $this->setLastFailureTime();

        if ($failureCount >= $this->failureThreshold) {
            // Open the circuit
            $this->setState(self::STATE_OPEN);
        }
    }

    /**
     * Get current circuit breaker state.
     */
    public function getState(): string
    {
        $redis = Redis::connection()->client();
        $state = $redis->get("{$this->circuitKey}:state");

        return $state !== null ? $state : self::STATE_CLOSED;
    }

    /**
     * Set circuit breaker state.
     */
    private function setState(string $state): void
    {
        $redis = Redis::connection()->client();
        $redis->set("{$this->circuitKey}:state", $state);
    }

    /**
     * Get failure count.
     */
    public function getFailureCount(): int
    {
        $redis = Redis::connection()->client();
        $count = $redis->get("{$this->circuitKey}:failures");

        return $count !== null ? (int) $count : 0;
    }

    /**
     * Increment failure count and return new count.
     */
    private function incrementFailureCount(): int
    {
        $redis = Redis::connection()->client();
        $count = $redis->incr("{$this->circuitKey}:failures");

        // Set expiration on failure count key (reset after timeout window)
        $redis->expire("{$this->circuitKey}:failures", $this->timeoutSeconds);

        return $count;
    }

    /**
     * Reset failure count.
     */
    private function resetFailureCount(): void
    {
        $redis = Redis::connection()->client();
        $redis->del("{$this->circuitKey}:failures");
    }

    /**
     * Get success count.
     */
    public function getSuccessCount(): int
    {
        $redis = Redis::connection()->client();
        $count = $redis->get("{$this->circuitKey}:successes");

        return $count !== null ? (int) $count : 0;
    }

    /**
     * Increment success count.
     */
    private function incrementSuccessCount(): void
    {
        $redis = Redis::connection()->client();
        $redis->incr("{$this->circuitKey}:successes");
    }

    /**
     * Get last failure timestamp.
     */
    public function getLastFailureTime(): int
    {
        $redis = Redis::connection()->client();
        $time = $redis->get("{$this->circuitKey}:last_failure");

        return $time !== null ? (int) $time : 0;
    }

    /**
     * Set last failure timestamp.
     */
    private function setLastFailureTime(): void
    {
        $redis = Redis::connection()->client();
        $redis->set("{$this->circuitKey}:last_failure", (string) time());
    }

    /**
     * Get half-open success count.
     */
    public function getHalfOpenSuccessCount(): int
    {
        $redis = Redis::connection()->client();
        $count = $redis->get("{$this->circuitKey}:half_open_successes");

        return $count !== null ? (int) $count : 0;
    }

    /**
     * Increment half-open success count.
     */
    private function incrementHalfOpenSuccessCount(): void
    {
        $redis = Redis::connection()->client();
        $redis->incr("{$this->circuitKey}:half_open_successes");
    }

    /**
     * Reset half-open success count.
     */
    private function resetHalfOpenSuccessCount(): void
    {
        $redis = Redis::connection()->client();
        $redis->del("{$this->circuitKey}:half_open_successes");
    }

    /**
     * Get circuit breaker statistics.
     *
     * @return array{state: string, failures: int, successes: int, last_failure_time: int, half_open_successes: int}
     */
    public function getStatistics(): array
    {
        return [
            'state' => $this->getState(),
            'failures' => $this->getFailureCount(),
            'successes' => $this->getSuccessCount(),
            'last_failure_time' => $this->getLastFailureTime(),
            'half_open_successes' => $this->getHalfOpenSuccessCount(),
        ];
    }
}
