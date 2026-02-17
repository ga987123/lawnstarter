# SWAPI Proxy

A production-ready monorepo that proxies the [Star Wars API](https://www.swapi.tech/) through a Laravel backend, with a React/TypeScript frontend.

## Quick Start

```bash
# If needed, install make and composer
brew install make
brew install composer

# Build project
make init

# Start all services
make up
```

Once running:

| Service  | URL                            |
| -------- | ------------------------------ |
| Frontend | http://localhost:5173          |
| Backend  | http://localhost:8080/api/docs |

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

- [backend/README.md](backend/README.md) 
- [frontend/README.md](frontend/README.md)

## Available Commands

| Command       | Description                                      |
| ------------- | ------------------------------------------------ |
| `make install`| Install Composer deps into backend/vendor (first run) |
| `make up`     | Start all services                               |
| `make down`   | Stop all services                                |
| `make logs`   | Tail logs from all services                      |
| `make init`   | Build containers, npm install, copy .env.example |
| `make test`   | Run all tests                                    |

## Tech Stack

| Layer    | Technology                                    |
| -------- | --------------------------------------------- |
| Backend  | PHP 8.4, Laravel 12, Predis                   |
| Frontend | React 19, TypeScript, Vite, TanStack Query    |
| Infra    | Docker, nginx, Redis                          |
| Quality  | Pest, PHPStan, Pint, Vitest, ESLint, behat    |
