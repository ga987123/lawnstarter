.PHONY: up down logs backend-shell frontend-shell test test-backend test-backend-coverage test-frontend test-frontend-coverage test-acceptance lint fresh build build-frontend

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

build-frontend:
	docker compose build frontend

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

test-backend-coverage:
	docker compose exec backend php artisan test --coverage

test-acceptance:
	docker compose exec -e ACCEPTANCE_BASE_URL=http://nginx:80 backend ./vendor/bin/behat

test-frontend:
	docker compose exec frontend npm run test:run

test-frontend-coverage:
	docker compose exec frontend npm run test:coverage