<?php

declare(strict_types=1);

return [

    'swapi' => [
        'base_url' => env('SWAPI_BASE_URL', 'https://www.swapi.tech/api'),
        'timeout' => (int) env('SWAPI_TIMEOUT', 5),
        'retry_times' => (int) env('SWAPI_RETRY_TIMES', 2),
        'retry_sleep' => (int) env('SWAPI_RETRY_SLEEP', 100),
    ],

    'statistics' => [
        'redis_query_log_key' => env('STATISTICS_QUERY_LOG_KEY', 'swapi:query_log'),
        'redis_query_counts_key' => env('STATISTICS_QUERY_COUNTS_KEY', 'swapi:query_counts'),
        'redis_cache_key' => env('STATISTICS_CACHE_KEY', 'swapi:statistics'),
        'cache_ttl' => (int) env('STATISTICS_CACHE_TTL', 360),
    ],

];
