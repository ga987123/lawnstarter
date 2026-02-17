<?php

declare(strict_types=1);

use App\Infrastructure\Clients\CircuitBreaker;
use Tests\Mocks\AppLoggerFake;

beforeEach(function (): void {
    /** @var \Tests\TestCase&object{circuitKey: string, logger: \Tests\Mocks\AppLoggerFake} $this */
    $this->circuitKey = 'test:circuit:' . uniqid('', true);
    $this->logger = new AppLoggerFake();
});

it('initial state is closed and check does not throw', function (): void {
    /** @var \Tests\TestCase&object{circuitKey: string, logger: \Tests\Mocks\AppLoggerFake} $this */
    $cb = new CircuitBreaker(
        circuitKey: $this->circuitKey,
        failureThreshold: 2,
        timeoutSeconds: 60,
        halfOpenSuccessThreshold: 2,
        logger: $this->logger,
    );

    expect($cb->getState())->toBe('closed');
    $cb->check();
});

it('opens after failure threshold and check throws', function (): void {
    /** @var \Tests\TestCase&object{circuitKey: string, logger: \Tests\Mocks\AppLoggerFake} $this */
    $cb = new CircuitBreaker(
        circuitKey: $this->circuitKey,
        failureThreshold: 2,
        timeoutSeconds: 60,
        halfOpenSuccessThreshold: 2,
        logger: $this->logger,
    );

    $cb->recordFailure();
    $cb->recordFailure();

    expect($cb->getState())->toBe('open');

    $cb->check();
})->throws(\RuntimeException::class, 'Circuit breaker is OPEN');

it('recordSuccess in closed state resets failure count', function (): void {
    /** @var \Tests\TestCase&object{circuitKey: string, logger: \Tests\Mocks\AppLoggerFake} $this */
    $cb = new CircuitBreaker(
        circuitKey: $this->circuitKey,
        failureThreshold: 3,
        timeoutSeconds: 60,
        halfOpenSuccessThreshold: 2,
        logger: $this->logger,
    );

    $cb->recordFailure();
    $cb->recordFailure();
    $cb->recordSuccess();

    expect($cb->getFailureCount())->toBe(0);
});

it('getStatistics returns state and counts', function (): void {
    /** @var \Tests\TestCase&object{circuitKey: string, logger: \Tests\Mocks\AppLoggerFake} $this */
    $cb = new CircuitBreaker(
        circuitKey: $this->circuitKey,
        failureThreshold: 5,
        timeoutSeconds: 60,
        halfOpenSuccessThreshold: 2,
        logger: $this->logger,
    );

    $stats = $cb->getStatistics();

    expect($stats)->toHaveKeys(['state', 'failures', 'successes', 'last_failure_time', 'half_open_successes'])
        ->and($stats['state'])->toBe('closed');
});
