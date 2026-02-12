.PHONY: up down logs backend-shell frontend-shell test lint fresh build

# ──────────────────────────────────────────────
# Docker Compose
# ──────────────────────────────────────────────

up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f

build:
	docker compose build --no-cache

refresh: down
	docker compose down -v
	docker compose build --no-cache
	docker compose up -d

# ──────────────────────────────────────────────
# Testing
# ──────────────────────────────────────────────

test: test-backend test-frontend

test-backend:
	docker compose exec backend php artisan test

test-frontend:
	docker compose exec frontend npm run test -- --run