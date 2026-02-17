<?php

declare(strict_types=1);

namespace Tests\Mocks;

use App\Domain\Contracts\AppLoggerInterface;

final class AppLoggerFake implements AppLoggerInterface
{
    /** @var list<array{level: string, message: string, context: array<string, mixed>}> */
    public array $logs = [];

    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'info', 'message' => $message, 'context' => $context];
    }

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'error', 'message' => $message, 'context' => $context];
    }

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'warning', 'message' => $message, 'context' => $context];
    }

    /** @param array<string, mixed> $context */
    public function debug(string $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'debug', 'message' => $message, 'context' => $context];
    }
}
