# Backend - SWAPI Proxy API

PHP 8.4 + Laravel 12 API-only application that proxies the Star Wars API (swapi.tech) and provides query statistics.

### Layer Responsibilities

| Layer          | Responsibility                                   |
|----------------|--------------------------------------------------|
| Domain         | DTOs, interfaces, exceptions. No framework deps. |
| Application    | Services, events, jobs, commands. Orchestration. |
| Infrastructure | Controllers, gateways, repositories. I/O.        |

### Folder Structure

```
app/
├── Domain/                    # Pure domain logic (no framework deps)
│   ├── Swapi/
│   │   ├── Contracts/         # SwapiGatewayInterface
│   │   ├── DTOs/              # PersonDto
│   │   └── Exceptions/        # SwapiNotFoundException, SwapiUnavailableException
│   └── Statistics/
│       ├── Contracts/         # QueryLogRepositoryInterface
│       └── DTOs/              # QueryStatisticsDto
├── Application/               # Use cases & orchestration
│   ├── Services/              # SwapiService, StatisticsService
│   ├── Events/                # QueryExecuted
│   ├── Listeners/             # RecordQueryMetrics (queued)
│   ├── Jobs/                  # RecomputeStatisticsJob
│   └── Console/Commands/      # ComputeStatisticsCommand
├── Infrastructure/            # Framework & external integrations
│   ├── Gateways/              # SwapiHttpGateway
│   ├── Repositories/          # RedisQueryLogRepository
│   └── Http/
│       ├── Controllers/       # HealthController, SwapiController, StatisticsController
│       └── Requests/          # GetPersonRequest
└── Providers/
    └── AppServiceProvider.php # Interface bindings
```

## API Endpoints

### GET /api/health

Returns service health status.

**Response 200:**
```json
{ "status": "ok" }
```

### GET /api/swapi/people/{id}

Fetches a Star Wars person from SWAPI and returns a normalized response.

**Parameters:**
- `id` (path, integer, min: 1) - The SWAPI person ID

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "name": "Luke Skywalker",
    "height": "172",
    "mass": "77",
    "birth_year": "19BBY",
    "gender": "male"
  }
}
```

**Response 404:**
```json
{
  "type": "https://httpstatuses.com/404",
  "title": "Not Found",
  "status": 404,
  "detail": "Person with ID 999 was not found in SWAPI."
}
```

**Response 502:**
```json
{
  "type": "https://httpstatuses.com/502",
  "title": "Bad Gateway",
  "status": 502,
  "detail": "SWAPI service is currently unavailable."
}
```

### GET /api/statistics

Returns query statistics, recomputed every 5 minutes.

**Response 200:**
```json
{
  "data": {
    "top_queries": [
      { "person_id": 1, "count": 42, "percentage": 35.0 }
    ],
    "average_response_time_ms": 150.25,
    "popular_hours": { "0": 5, "1": 3, "14": 28 },
    "total_queries": 120,
    "computed_at": "2026-01-15T10:30:00+00:00"
  }
}
```


### Redis Keys

| Key                  | Type       | Purpose                              |
|----------------------|------------|--------------------------------------|
| `swapi:query_log`    | List       | JSON entries with query metadata     |
| `swapi:query_counts` | Sorted Set | Person ID -> query count             |
| `swapi:statistics`   | String     | Cached computed statistics (TTL 6m)  |

## Error Handling

All errors follow **RFC 9457 Problem Details** format:

```json
{
  "type": "https://httpstatuses.com/{status}",
  "title": "Human-readable title",
  "status": 422,
  "detail": "Detailed error description",
  "errors": {}
}
```

| Exception                | HTTP Status | When                          |
|--------------------------|-------------|-------------------------------|
| `SwapiNotFoundException` | 404         | Person not found in SWAPI     |
| `SwapiUnavailableException` | 502     | SWAPI connection/request error|
| `ValidationException`    | 422         | Invalid request parameters    |

## Testing

Tests use **Pest** (PHPUnit wrapper) with mocked dependencies.

```bash
# Run all tests
docker compose exec backend php artisan test

# Run specific test
docker compose exec backend ./vendor/bin/pest --filter="health"
```

## Code Quality

```bash
# Format code (Laravel Pint)
docker compose exec backend ./vendor/bin/pint

# Static analysis (PHPStan level 8)
docker compose exec backend ./vendor/bin/phpstan analyse

# Rector (dry-run)
docker compose exec backend ./vendor/bin/rector process --dry-run
```

## Configuration

Key environment variables (see `.env.example`):

| Variable           | Default                          | Description              |
|--------------------|----------------------------------|--------------------------|
| `REDIS_HOST`       | `redis`                          | Redis hostname           |
| `CACHE_STORE`      | `redis`                          | Cache driver             |
| `QUEUE_CONNECTION` | `redis`                          | Queue driver             |
| `SWAPI_BASE_URL`   | `https://www.swapi.tech/api`     | SWAPI base URL           |
| `SWAPI_TIMEOUT`    | `5`                              | HTTP timeout (seconds)   |
| `SWAPI_RETRY_TIMES`| `2`                              | Number of retries        |

## OpenAPI Specification

The API specification is available at `docs/openapi.yaml` and can be viewed with any OpenAPI-compatible tool (Swagger UI, Redocly, etc.).

**Swagger UI** is available at `/api/docs` when the backend is running (e.g. `http://localhost:8080/api/docs`).
