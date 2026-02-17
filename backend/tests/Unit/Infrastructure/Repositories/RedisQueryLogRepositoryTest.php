<?php

declare(strict_types=1);

use App\Infrastructure\Repositories\RedisQueryLogRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Tests\Mocks\AppLoggerFake;

beforeEach(function (): void {
    $prefix = 'test:stats:' . uniqid('', true);
    Config::set('services.statistics.redis_query_log_key', $prefix . ':query_log');
    Config::set('services.statistics.redis_query_counts_key', $prefix . ':query_counts');
    Config::set('services.statistics.redis_film_query_log_key', $prefix . ':film_query_log');
    Config::set('services.statistics.redis_film_query_counts_key', $prefix . ':film_query_counts');
    Config::set('services.statistics.redis_search_log_key', $prefix . ':search_log');
    Config::set('services.statistics.redis_search_counts_key', $prefix . ':search_counts');
    Config::set('services.statistics.redis_cache_key', $prefix . ':cache');
    Config::set('services.statistics.cache_ttl', 60);
});

it('recordQuery pushes entry and increments count', function (): void {
    $prefix = 'test:stats:' . uniqid('', true);
    Config::set('services.statistics.redis_query_log_key', $prefix . ':query_log');
    Config::set('services.statistics.redis_query_counts_key', $prefix . ':query_counts');
    Config::set('services.statistics.redis_film_query_log_key', $prefix . ':film_query_log');
    Config::set('services.statistics.redis_film_query_counts_key', $prefix . ':film_query_counts');
    Config::set('services.statistics.redis_search_log_key', $prefix . ':search_log');
    Config::set('services.statistics.redis_search_counts_key', $prefix . ':search_counts');
    Config::set('services.statistics.redis_cache_key', $prefix . ':cache');
    Config::set('services.statistics.cache_ttl', 60);

    $logger = new AppLoggerFake();
    $repo = new RedisQueryLogRepository($logger);

    $repo->recordQuery(1, 100.5);

    $redis = Redis::connection()->client();
    $len = $redis->llen($prefix . ':query_log');
    $count = $redis->zscore($prefix . ':query_counts', '1');

    expect($len)->toBe(1)->and((int) $count)->toBe(1);
});

it('getCachedStatistics returns null when cache empty', function (): void {
    $logger = new AppLoggerFake();
    $repo = new RedisQueryLogRepository($logger);

    $result = $repo->getCachedStatistics();

    expect($result)->toBeNull();
});

it('cacheStatistics and getCachedStatistics roundtrip', function (): void {
    $logger = new AppLoggerFake();
    $repo = new RedisQueryLogRepository($logger);
    $dto = \Tests\Mocks\StatisticsMocks::queryStatisticsDto(['totalQueries' => 7]);

    $repo->cacheStatistics($dto);
    $cached = $repo->getCachedStatistics();

    expect($cached)->not->toBeNull();
    assert($cached !== null);
    expect($cached->totalQueries)->toBe(7);
});

it('computeStatistics with empty Redis returns zero totals', function (): void {
    $logger = new AppLoggerFake();
    $repo = new RedisQueryLogRepository($logger);

    $dto = $repo->computeStatistics();

    expect($dto->totalQueries)->toBe(0)
        ->and($dto->averageResponseTimeMs)->toBe(0.0)
        ->and($dto->popularHours)->toHaveCount(24);
});
