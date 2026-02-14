<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use App\Domain\Statistics\DTOs\QueryStatisticsDto;
use Illuminate\Support\Facades\Redis;

final class RedisQueryLogRepository implements QueryLogRepositoryInterface
{
    private readonly string $queryLogKey;

    private readonly string $queryCountsKey;

    private readonly string $searchLogKey;

    private readonly string $searchCountsKey;

    private readonly string $statisticsCacheKey;

    private readonly int $statisticsTtl;

    public function __construct()
    {
        $this->queryLogKey = (string) config('services.statistics.redis_query_log_key');
        $this->queryCountsKey = (string) config('services.statistics.redis_query_counts_key');
        $this->searchLogKey = (string) config('services.statistics.redis_search_log_key');
        $this->searchCountsKey = (string) config('services.statistics.redis_search_counts_key');
        $this->statisticsCacheKey = (string) config('services.statistics.redis_cache_key');
        $this->statisticsTtl = (int) config('services.statistics.cache_ttl');
    }

    public function recordQuery(int $personId, float $responseTimeMs): void
    {
        $entry = json_encode([
            'person_id' => $personId,
            'response_time_ms' => $responseTimeMs,
            'timestamp' => now()->toIso8601String(),
            'hour' => now()->hour,
        ], JSON_THROW_ON_ERROR);

        $redis = Redis::connection()->client();
        $redis->rpush($this->queryLogKey, [$entry]);
        $redis->zincrby($this->queryCountsKey, 1, (string) $personId);
    }

    public function recordSearchQuery(string $searchType, string $query, float $responseTimeMs, int $resultCount): void
    {
        $entry = json_encode([
            'search_type' => $searchType,
            'query' => $query,
            'response_time_ms' => $responseTimeMs,
            'result_count' => $resultCount,
            'timestamp' => now()->toIso8601String(),
            'hour' => now()->hour,
        ], JSON_THROW_ON_ERROR);

        $countKey = $searchType . ':' . mb_strtolower(trim($query));

        $redis = Redis::connection()->client();
        $redis->rpush($this->searchLogKey, [$entry]);
        $redis->zincrby($this->searchCountsKey, 1, $countKey);
    }

    public function computeStatistics(): QueryStatisticsDto
    {
        $redis = Redis::connection()->client();

        $topRaw = $redis->zrevrange($this->queryCountsKey, 0, 4, ['WITHSCORES' => true]);

        $allCounts = $redis->zrevrange($this->queryCountsKey, 0, -1, ['WITHSCORES' => true]);
        $totalQueries = array_sum(array_map('intval', array_values($allCounts)));

        $topQueries = [];
        foreach ($topRaw as $personId => $count) {
            $countInt = (int) $count;
            $topQueries[] = [
                'person_id' => (int) $personId,
                'count' => $countInt,
                'percentage' => $totalQueries > 0
                    ? round(($countInt / $totalQueries) * 100, 2)
                    : 0.0,
            ];
        }

        // Compute average response time and popular hours from log entries
        $logLength = $redis->llen($this->queryLogKey);
        $totalResponseTime = 0.0;
        $hourCounts = array_fill(0, 24, 0);
        $entryCount = 0;

        // Process in batches to avoid memory issues
        $batchSize = 1000;
        for ($offset = 0; $offset < $logLength; $offset += $batchSize) {
            $entries = $redis->lrange($this->queryLogKey, $offset, $offset + $batchSize - 1);

            foreach ($entries as $entry) {
                $decoded = json_decode($entry, true, 512, JSON_THROW_ON_ERROR);
                $totalResponseTime += $decoded['response_time_ms'];
                $hourCounts[$decoded['hour']]++;
                $entryCount++;
            }
        }

        $averageResponseTime = $entryCount > 0 ? $totalResponseTime / $entryCount : 0.0;

        return new QueryStatisticsDto(
            topQueries: $topQueries,
            averageResponseTimeMs: $averageResponseTime,
            popularHours: $hourCounts,
            totalQueries: (int) $totalQueries,
            computedAt: now()->toIso8601String(),
        );
    }

    public function getCachedStatistics(): ?QueryStatisticsDto
    {
        $redis = Redis::connection()->client();

        $cached = $redis->get($this->statisticsCacheKey);

        if ($cached === null) {
            return null;
        }

        $data = json_decode($cached, true, 512, JSON_THROW_ON_ERROR);

        return new QueryStatisticsDto(
            topQueries: $data['top_queries'],
            averageResponseTimeMs: $data['average_response_time_ms'],
            popularHours: $data['popular_hours'],
            totalQueries: $data['total_queries'],
            computedAt: $data['computed_at'],
        );
    }

    public function cacheStatistics(QueryStatisticsDto $statistics): void
    {
        $redis = Redis::connection()->client();

        $redis->setex(
            $this->statisticsCacheKey,
            $this->statisticsTtl,
            json_encode($statistics->toArray(), JSON_THROW_ON_ERROR),
        );
    }
}
