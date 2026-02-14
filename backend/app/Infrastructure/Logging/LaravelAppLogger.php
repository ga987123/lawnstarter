<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use App\Domain\Contracts\AppLoggerInterface;
use Illuminate\Support\Facades\Log;

final class LaravelAppLogger implements AppLoggerInterface
{
    private const CHANNEL = 'stack';

    public function info(string $message, array $context = []): void
    {
        Log::channel(self::CHANNEL)->info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        Log::channel(self::CHANNEL)->error($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        Log::channel(self::CHANNEL)->warning($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        Log::channel(self::CHANNEL)->debug($message, $context);
    }
}
