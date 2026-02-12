# SWAPI Proxy

A production-ready monorepo skeleton that proxies the [Star Wars API](https://www.swapi.tech/) through a Laravel backend, with a React/TypeScript frontend.

## Quick Start

```bash
# Start all services
make up

# Or without make:
docker compose up -d
```

Once running:

| Service  | URL                          |
|----------|------------------------------|
| Frontend | http://localhost:5173            |
| Backend  | http://localhost:8080/api/docs   |

## Project Structure

```
.
├── backend/          # PHP 8.4 + Laravel (API-only)
├── frontend/         # React + TypeScript + Vite
├── docker-compose.yml
├── Makefile
└── README.md
```

See each folder's README for detailed architecture:

- [backend/README.md](backend/README.md) - Layered architecture, API docs, testing
- [frontend/README.md](frontend/README.md) - Module boundaries, component structure

## Available Commands

| Command              | Description                          |
|----------------------|--------------------------------------|
| `make up`            | Start all services                   |
| `make down`          | Stop all services                    |
| `make logs`          | Tail logs from all services          |
| `make build`         | Rebuild all containers (no cache)    |


## Error Format

All errors follow [RFC 9457 - Problem Details](https://www.rfc-editor.org/rfc/rfc9457):

```json
{
  "type": "https://httpstatuses.com/404",
  "title": "Not Found",
  "status": 404,
  "detail": "Person with ID 999 was not found in SWAPI."
}
```

## Tech Stack

| Layer    | Technology                                    |
|----------|-----------------------------------------------|
| Backend  | PHP 8.4, Laravel 12, Predis                   |
| Frontend | React 19, TypeScript, Vite, TanStack Query    |
| Infra    | Docker, nginx, Redis 7                        |
| Quality  | Pest, PHPStan, Pint, Vitest, ESLint, Prettier |
