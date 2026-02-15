<?php

declare(strict_types=1);

return [

    'swapi' => [
        'base_url' => env('SWAPI_BASE_URL', 'https://www.swapi.tech/api'),
        'timeout' => (int) env('SWAPI_TIMEOUT', 5),
        'retry_times' => (int) env('SWAPI_RETRY_TIMES', 2),
        'retry_sleep' => (int) env('SWAPI_RETRY_SLEEP', 100),
        'circuit_failure_threshold' => (int) env('SWAPI_CIRCUIT_FAILURE_THRESHOLD', 5),
        'circuit_timeout_seconds' => (int) env('SWAPI_CIRCUIT_TIMEOUT_SECONDS', 60),
        'circuit_half_open_success_threshold' => (int) env('SWAPI_CIRCUIT_HALF_OPEN_SUCCESS_THRESHOLD', 2),
    ],

    'statistics' => [
        'redis_query_log_key' => env('STATISTICS_QUERY_LOG_KEY', 'swapi:query_log'),
        'redis_query_counts_key' => env('STATISTICS_QUERY_COUNTS_KEY', 'swapi:query_counts'),
        'redis_film_query_log_key' => env('STATISTICS_FILM_QUERY_LOG_KEY', 'swapi:film_query_log'),
        'redis_film_query_counts_key' => env('STATISTICS_FILM_QUERY_COUNTS_KEY', 'swapi:film_query_counts'),
        'redis_search_log_key' => env('STATISTICS_SEARCH_LOG_KEY', 'swapi:search_log'),
        'redis_search_counts_key' => env('STATISTICS_SEARCH_COUNTS_KEY', 'swapi:search_counts'),
        'redis_cache_key' => env('STATISTICS_CACHE_KEY', 'swapi:statistics'),
        'cache_ttl' => (int) env('STATISTICS_CACHE_TTL', 360),
    ],

];
