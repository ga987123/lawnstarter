<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\AppLoggerInterface;
use App\Domain\Statistics\Contracts\QueryLogRepositoryInterface;
use App\Domain\Statistics\DTOs\QueryStatisticsDto;
use Illuminate\Support\Facades\Redis;

final class RedisQueryLogRepository implements QueryLogRepositoryInterface
{
    private readonly string $queryLogKey;

    private readonly string $queryCountsKey;

    private readonly string $filmQueryLogKey;

    private readonly string $filmQueryCountsKey;

    private readonly string $searchLogKey;

    private readonly string $searchCountsKey;

    private readonly string $statisticsCacheKey;

    private readonly int $statisticsTtl;

    public function __construct(
        private readonly AppLoggerInterface $logger,
    ) {
        $this->queryLogKey = (string) config('services.statistics.redis_query_log_key');
        $this->queryCountsKey = (string) config('services.statistics.redis_query_counts_key');
        $this->filmQueryLogKey = (string) config('services.statistics.redis_film_query_log_key');
        $this->filmQueryCountsKey = (string) config('services.statistics.redis_film_query_counts_key');
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

    public function recordFilmQuery(int $filmId, float $responseTimeMs): void
    {
        $entry = json_encode([
            'film_id' => $filmId,
            'response_time_ms' => $responseTimeMs,
            'timestamp' => now()->toIso8601String(),
            'hour' => now()->hour,
        ], JSON_THROW_ON_ERROR);

        $redis = Redis::connection()->client();
        $redis->rpush($this->filmQueryLogKey, [$entry]);
        $redis->zincrby($this->filmQueryCountsKey, 1, (string) $filmId);
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
        $this->logger->info('Computing statistics from Redis');

        $redis = Redis::connection()->client();
        $batchSize = 1000;

        $totalDetailQueries = $this->sumSortedSetScores($redis, $this->queryCountsKey);
        $totalSearchQueries = $this->sumSortedSetScores($redis, $this->searchCountsKey);
        $totalFilmQueries = $this->sumSortedSetScores($redis, $this->filmQueryCountsKey);
        $totalQueries = $totalDetailQueries + $totalFilmQueries + $totalSearchQueries;

        $topSearchRaw = $redis->zrevrange($this->searchCountsKey, 0, 4, ['WITHSCORES' => true]);
        $topSearchQueries = $this->parseTopSearchQueries($topSearchRaw ?? [], $totalSearchQueries);

        $agg1 = $this->aggregateLogEntries($redis, $this->queryLogKey, $batchSize);
        $agg2 = $this->aggregateLogEntries($redis, $this->filmQueryLogKey, $batchSize);
        $agg3 = $this->aggregateLogEntries($redis, $this->searchLogKey, $batchSize);

        $totalResponseTime = $agg1['total_response_time'] + $agg2['total_response_time'] + $agg3['total_response_time'];
        $entryCount = $agg1['entry_count'] + $agg2['entry_count'] + $agg3['entry_count'];

        $hourCounts = array_fill(0, 24, 0);
        for ($h = 0; $h < 24; $h++) {
            $hourCounts[$h] = $agg1['hour_counts'][$h] + $agg2['hour_counts'][$h] + $agg3['hour_counts'][$h];
        }

        $popularHours = array_map(
            fn(int $h) => ['hour' => $h, 'total_count' => $hourCounts[$h]],
            range(0, 23),
        );

        $averageResponseTime = $entryCount > 0 ? $totalResponseTime / $entryCount : 0.0;

        $this->logger->info('Statistics computed', [
            'total_queries' => $totalQueries,
            'entry_count' => $entryCount,
        ]);

        return new QueryStatisticsDto(
            averageResponseTimeMs: $averageResponseTime,
            popularHours: $popularHours,
            totalQueries: $totalQueries,
            computedAt: now()->toIso8601String(),
            topSearchQueries: $topSearchQueries,
        );
    }

    public function getCachedStatistics(): ?QueryStatisticsDto
    {
        $redis = Redis::connection()->client();

        $cached = $redis->get($this->statisticsCacheKey);

        if ($cached === null) {
            $this->logger->debug('Statistics cache miss');
            return null;
        }

        $this->logger->debug('Statistics cache hit');
        $data = json_decode($cached, true, 512, JSON_THROW_ON_ERROR);

        $popularHours = $this->normalizePopularHours($data['popular_hours'] ?? []);
        $topSearchQueries = $data['top_search_queries'] ?? [];

        return new QueryStatisticsDto(
            averageResponseTimeMs: $data['average_response_time_ms'],
            popularHours: $popularHours,
            totalQueries: $data['total_queries'],
            computedAt: $data['computed_at'],
            topSearchQueries: $topSearchQueries,
        );
    }

    private function sumSortedSetScores(object $redis, string $key): int
    {
        $result = $redis->zrevrange($key, 0, -1, ['WITHSCORES' => true]);
        return (int) array_sum(array_map('intval', array_values($result ?? [])));
    }

    /**
     * @param \Redis $redis
     * @return array{total_response_time: float, entry_count: int, hour_counts: array<int, int>}
     */
    private function aggregateLogEntries(object $redis, string $logKey, int $batchSize): array
    {
        $totalResponseTime = 0.0;
        $hourCounts = array_fill(0, 24, 0);
        $entryCount = 0;

        // chuncking to not overload memory
        $length = $redis->llen($logKey);
        for ($offset = 0; $offset < $length; $offset += $batchSize) {
            $entries = $redis->lrange($logKey, $offset, $offset + $batchSize - 1);
            foreach ($entries as $entry) {
                $decoded = json_decode($entry, true, 512, JSON_THROW_ON_ERROR);
                $totalResponseTime += $decoded['response_time_ms'];
                $hourCounts[$decoded['hour']]++;
                $entryCount++;
            }
        }

        return [
            'total_response_time' => $totalResponseTime,
            'entry_count' => $entryCount,
            'hour_counts' => $hourCounts,
        ];
    }

    /**
     * @param  array<string, string>  $topSearchRaw  From zrevrange WITHSCORES (member => score)
     * @return list<array{search_type: string, query: string, count: int, percentage: float}>
     */
    private function parseTopSearchQueries(array $topSearchRaw, int $totalSearchQueries): array
    {
        $result = [];
        foreach ($topSearchRaw as $countKey => $score) {
            $pos = strpos($countKey, ':');
            $searchType = $pos !== false ? substr($countKey, 0, $pos) : $countKey;
            $query = $pos !== false ? substr($countKey, $pos + 1) : '';
            $countInt = (int) $score;
            $result[] = [
                'search_type' => $searchType,
                'query' => $query,
                'count' => $countInt,
                'percentage' => $totalSearchQueries > 0
                    ? round(($countInt / $totalSearchQueries) * 100, 2)
                    : 0.0,
            ];
        }
        return $result;
    }

    /**
     * Normalize popular_hours to array of { hour, total_count }.
     * Accepts new format (list of objects) or old format (map hour => count).
     *
     * @param  array<int|string, mixed>  $popularHours
     * @return list<array{hour: int, total_count: int}>
     */
    private function normalizePopularHours(array $popularHours): array
    {
        $first = reset($popularHours);
        if ($first === false) {
            return array_map(fn(int $h) => ['hour' => $h, 'total_count' => 0], range(0, 23));
        }
        if (is_array($first) && array_key_exists('hour', $first) && array_key_exists('total_count', $first)) {
            return $popularHours;
        }
        $result = [];
        for ($h = 0; $h < 24; $h++) {
            $result[] = [
                'hour' => $h,
                'total_count' => (int) ($popularHours[$h] ?? $popularHours[(string) $h] ?? 0),
            ];
        }
        return $result;
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
